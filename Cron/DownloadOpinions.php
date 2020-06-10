<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Helper\Data;
use TrustMate\Opinions\Logger\Logger;
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
     * @var Logger
     */
    protected $logger;

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
     * @param Logger                     $logger
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
        RatingFactory              $ratingFactory,
        Logger                     $logger
    ) {
        $this->helper            = $helper;
        $this->api               = $api;
        $this->opinion           = $opinion;
        $this->timezone          = $timezone;
        $this->reviewFactory     = $reviewFactory;
        $this->storeManager      = $storeManager;
        $this->productRepository = $productRepository;
        $this->reviewsCollection = $reviewsCollection;
        $this->ratingFactory     = $ratingFactory;
        $this->logger            = $logger;
    }

    /**
     * Download and saving opinions from external api
     */
    public function execute()
    {
        if (!$this->helper->isProductsOpinionsEnabled()) {
            return;
        }

        $date = $this->timezone->date();
        $endDate = $date->format('Y-m-d H:i:s');
        $newest = $this->getNewestOpinion();

        $data = array(
            "start" => $newest->getCreatedAt(),
            "end" => $endDate,
            'per_page' => 20
        );
        $page = 1;
        $stores = $this->getStores();
        $storeId = $this->storeManager->getStore()->getId();

        do {
            $data['page'] = $page;
            $opinions = $this->api->getProductReview($data);

            if (!is_array($opinions) || !isset($opinions['pages']) || !isset($opinions['items'])) {
                return;
            }

            foreach ($opinions['items'] as $opinion) {
                if (!isset($opinion['product']) || !isset($opinion['product']['local_id'])
                    || !isset($opinion['public_identifier']) || !isset($opinion['body'])
                ) {
                    continue;
                }

                $opinion['product'] = $opinion['product']['local_id'];

                try {
                    if ($this->opinionExist($opinion['public_identifier'])) {
                        continue;
                    }

                    $trustmateOpinion = $this->saveTrustmateOpinion($opinion);
                    $product = $this->productRepository->get($trustmateOpinion->getProduct());

                    $reviewData = array(
                        'title' => Data::OPINION_TITLE,
                        'detail' => $trustmateOpinion->getBody(),
                        'nickname' => $trustmateOpinion->getAuthorName(),
                        'grade' => $trustmateOpinion->getGrade()
                    );

                    $this->saveMagentoOpinion($reviewData, $product->getId(), $stores, $storeId);
                }
                catch (NoSuchEntityException $e) {
                    $this->logger->critical('Product with sku ' . $opinion['product'] . ' not exist');
                    continue;
                }
                catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                    continue;
                };


            }
        } while (isset($opinions['pages']) && $page++ < $opinions['pages']);
    }

    /**
     * @return array
     */
    private function getStores() {
        if ($id = $this->helper->getOpinionsStoreId()) {
            return [$id];
        }

        $stores = $this->storeManager->getStores();
        $ids = array();

        foreach ($stores as $store) {
            $ids[] = $store->getId();
        }

        return $ids;
    }

    /**
     * @return DataObject
     */
    private function getNewestOpinion() {
        $collection = $this->reviewsCollection->create();
        $collection->addFieldToSelect('created_at');
        $collection->setOrder('created_at', 'DESC');

        return $collection->getFirstItem();
    }

    /**
     * @param $data
     * @return \TrustMate\Opinions\Model\ProductOpinions
     * @throws \Exception
     */
    private function saveTrustmateOpinion($data) {
        $opinion = $this->opinion->create();
        $opinion->setData($data);
        $opinion->save();

        return $opinion;
    }

    /**
     * @param array $data
     * @param $productId
     * @param $stores
     * @param $storeId
     */
    private function saveMagentoOpinion($data, $productId, $stores, $storeId) {
        $review = $this->reviewFactory->create()->setData($data);
        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
            ->setEntityPkValue($productId)
            ->setStatusId(Review::STATUS_APPROVED)
            ->setStoreId($storeId)
            ->setStores($stores)
            ->save();

        if ($review->getEntityId()) {
            $this->saveRating($data, $productId, $review);
        }

    }

    /**
     * @param array $data
     * @param $productId
     * @param $review
     */
    private function saveRating($data, $productId, $review) {
        $trustmateRating = $this->ratingFactory->create()
            ->getResourceCollection()
            ->addFieldToFilter('rating_code', Data::TRUSTMATE_CODE)
            ->getFirstItem()
            ->getData();

        $this->ratingFactory->create()
            ->setRatingId($trustmateRating['rating_id'])
            ->setReviewId($review->getId())
            ->addOptionVote($data['grade'], $productId);

        $review->aggregate();
    }

    /**
     * @param $identifier
     * @return int
     */
    private function opinionExist($identifier) {
        $collection = $this->reviewsCollection->create();
        $collection->addFieldToFilter('public_identifier', $identifier);

        return $collection->getSize();
    }
}
