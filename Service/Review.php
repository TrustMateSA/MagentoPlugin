<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use TrustMate\Opinions\Http\Request\ProductReview;
use TrustMate\Opinions\Logger\Logger;
use TrustMate\Opinions\Model\Config\Data;
use TrustMate\Opinions\Model\ProductReviewFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductReview as ProductReviewResource;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Model\Store;

class Review
{
    /**
     * @var ProductReview
     */
    private $httpProductReview;

    /**
     * @var ReviewModel
     */
    private $reviewModel;

    /**
     * @var ProductReviewFactory
     */
    private $productReview;

    /**
     * @var ProductReviewResource
     */
    private $productReviewResource;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Data
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Store
     */
    private $store;

    /**
     * @param ProductReview              $httpProductReview
     * @param ReviewModel                $reviewModel
     * @param ProductReviewFactory       $productReview
     * @param ProductReviewResource      $productReviewResource
     * @param Logger                     $logger
     * @param ProductRepositoryInterface $productRepository
     * @param Data                       $config
     */
    public function __construct(
        ProductReview              $httpProductReview,
        ReviewModel                $reviewModel,
        ProductReviewFactory       $productReview,
        ProductReviewResource      $productReviewResource,
        Logger                     $logger,
        ProductRepositoryInterface $productRepository,
        Data                       $config,
        Store                      $store
    ) {
        $this->httpProductReview     = $httpProductReview;
        $this->reviewModel           = $reviewModel;
        $this->productReview         = $productReview;
        $this->productReviewResource = $productReviewResource;
        $this->logger                = $logger;
        $this->productRepository     = $productRepository;
        $this->config                = $config;
        $this->store                 = $store;
    }

    /**
     * Add review
     *
     * @param array $preparedData
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function add(array $preparedData)
    {
        $response = $this->httpProductReview->sendRequest($preparedData);
        if (isset($response['status'])) {
            $this->logger->error($response['message']);

            return;
        }

        $this->save($response);
    }

    /**
     * add review translation
     *
     * @param array $preparedData
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function addTranslation(array $preparedData)
    {
        $response = $this->httpProductReview->sendRequest($preparedData, true);

        if (isset($response['status'])) {
            $this->logger->error($response['message']);

            return;
        }

        $this->save($response, true);
    }

    /**
     * Save review
     *
     * @param array $reviews
     * @param bool  $translation
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function save(array $reviews, bool $translation = false)
    {
        foreach ($reviews['items'] as $item) {
            $originalBody  = (!isset($item['originalBody']) || !$item['originalBody']) ? null : $item['originalBody'];
            $productId     = $item['product']['localId'];
            if ($this->config->isFixLocalIdEnabled()) {
                try {
                    $product   = $this->productRepository->get($item['product']['localId']);
                    $productId = $product->getId();
                } catch (NoSuchEntityException $e) {
                    $this->logger->info($e->getMessage());
                }
            }

            $data = [
                'id' => $this->reviewModel->checkIfExists($item['publicIdentifier'], $originalBody, $translation),
                'created_at' => $item['createdAt'],
                'updated_at' => $item['updatedAt'],
                'grade' => $item['grade'],
                'author_email' => $item['author']['email'],
                'author_name' => $item['author']['name'],
                'product' => $productId,
                'body' => !$item['body'] ? ' ' : $item['body'],
                'public_identifier' => $item['publicIdentifier'],
                'language' => $item['language'],
                'original_body' => $originalBody,
                'order_increment_id' => $item['orderIdentifier'],
                'gtin_code' => $item['product']['gtin'],
                'mpn_code' => $item['product']['mpn'],
                'status' => $item['status']
            ];

            $productReview = $this->productReview->create();
            $productReview->setData($data);
            $this->productReviewResource->save($productReview);

            foreach ($this->store->getStoreLocales()[$data['language']] as $storeLocale) {
                $reviewData = [
                    'trustmate_review_id' => $productReview->getId(),
                    'title' => 'Opinia z TrustMate',
                    'detail' => $data['body'],
                    'nickname' => $data['author_name'],
                    'grade' => $data['grade'],
                    'entity_pk_value' => $data['product'],
                    'review_store_id' => $storeLocale
                ];

                $this->reviewModel->saveReviewToMagento($reviewData, $storeLocale);
            }
        }
    }
}
