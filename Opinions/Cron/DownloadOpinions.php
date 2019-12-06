<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Helper\Data;
use TrustMate\Opinions\Model\Api\Api;
use TrustMate\Opinions\Model\ProductOpinionsFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductOpinions\CollectionFactory as ReviewsCollectionFactory;

/**
 * Class DownloadOpinions
 * @package TrustMate\Opinions\Cron
 */
class DownloadOpinions
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var ProductOpinionsFactory
     */
    protected $opinion;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ReviewsCollectionFactory
     */
    private $reviewsCollection;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RatingFactory
     */
    protected $ratingFactory;

    /**
     * DownloadOpinions constructor.
     * @param Api                        $api
     * @param ProductOpinionsFactory     $opinion
     * @param TimezoneInterface          $timezone
     * @param ReviewFactory              $reviewFactory
     * @param StoreManagerInterface      $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewsCollectionFactory   $reviewsCollection
     * @param Data                       $helper
     * @param RatingFactory              $ratingFactory
     */
    public function __construct(
        Api                        $api,
        ProductOpinionsFactory     $opinion,
        TimezoneInterface          $timezone,
        ReviewFactory              $reviewFactory,
        StoreManagerInterface      $storeManager,
        ProductRepositoryInterface $productRepository,
        ReviewsCollectionFactory   $reviewsCollection,
        Data                       $helper,
        RatingFactory              $ratingFactory
    ) {
        $this->helper   = $helper;
        $this->api      = $api;
        $this->opinion  = $opinion;
        $this->timezone = $timezone;
        $this->reviewFactory = $reviewFactory;
        $this->storeManager  = $storeManager;
        $this->productRepository = $productRepository;
        $this->reviewsCollection = $reviewsCollection;
        $this->ratingFactory = $ratingFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute() {
        if ($this->helper->isProductsOpinionsEnabled()) {
            $date = $this->timezone->date();
            $endDate = $date->format('Y-m-d H:i:s');

            $collection = $this->reviewsCollection->create();
            $collection->addFieldToSelect('created_at');
            $collection->setOrder('created_at' , 'DESC');
            $newest = $collection->getFirstItem();
            $fromDate = NULL;

            if ($newest->getCreatedAt()) {
                $fromDate = $newest->getCreatedAt();
                $fromDate = new \DateTime("@" . strtotime($fromDate));
                $fromDate->modify("+1 second");
                $fromDate = $fromDate->format("Y-m-d H:i:s");
            }

            $opinions = $this->api->getProductReview(
                array(
                    "start" => $fromDate,
                    "end" => $endDate,
                    "page" => 1,
                    'per_page' => 20
                )
            );

            if (is_array($opinions) && isset($opinions['items'])) {
                foreach ($opinions['items'] as $opinion) {
                    if (isset($opinion['product']) && isset($opinion['product']['gtin'])) {
                        $opinion['product'] = $opinion['product']['gtin'];
                    }

                    $reviewModel = $this->opinion->create();
                    $reviewModel->setData($opinion);
                    $reviewModel->save();

                    $reviewData = array(
                        'title' => Data::OPINION_TITLE,
                        'detail' => $reviewModel->getBody(),
                        'nickname' => $reviewModel->getAuthorName(),
                    );

                    $product = $this->productRepository->get($reviewModel->getProduct());

                    if ($product->getId()) {
                        $review = $this->reviewFactory->create()->setData($reviewData);
                        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setStatusId(Review::STATUS_APPROVED)
                            ->setStoreId($this->storeManager->getStore()->getId())
                            ->setStores($this->getStoresId())
                            ->save();

                        $trustmateRating = $this->ratingFactory->create()
                            ->getResourceCollection()
                            ->addFieldToFilter('rating_code', Data::TRUSTMATE_CODE)
                            ->getFirstItem()
                            ->getData()
                        ;

                        $this->ratingFactory->create()
                            ->setRatingId($trustmateRating['rating_id'])
                            ->setReviewId($review->getId())
                            ->addOptionVote($reviewModel->getGrade(), $product->getId());
                        ;

                        $review->aggregate();
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getStoresId() {
        $stores = $this->storeManager->getStores();
        $ids = array();

        foreach ($stores as $store) {
            $ids[] = $store->getId();
        }

        return $ids;
    }
}