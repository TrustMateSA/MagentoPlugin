<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Service;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use TrustMate\Opinions\Http\Request\ProductReview;
use TrustMate\Opinions\Model\ProductReviewFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductReview as ProductReviewResource;
use TrustMate\Opinions\Model\Review as ReviewModel;
use TrustMate\Opinions\Logger\Logger;

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
     * @param ProductReview $httpProductReview
     * @param ReviewModel $reviewModel
     * @param ProductReviewFactory $productReview
     * @param ProductReviewResource $productReviewResource
     * @param Logger $logger
     */
    public function __construct(
        ProductReview         $httpProductReview,
        ReviewModel           $reviewModel,
        ProductReviewFactory  $productReview,
        ProductReviewResource $productReviewResource,
        Logger                $logger
    ) {
        $this->httpProductReview = $httpProductReview;
        $this->reviewModel = $reviewModel;
        $this->productReview = $productReview;
        $this->productReviewResource = $productReviewResource;
        $this->logger = $logger;
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
     * @param bool $translation
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function save(array $reviews, bool $translation = false)
    {
        foreach ($reviews['items'] as $item) {
            $productReview = $this->productReview->create();
            $originalBody = (!isset($item['originalBody'])) ? null : $item['originalBody'];
            $data = [
                'created_at' => $item['createdAt'],
                'updated_at' => $item['updatedAt'],
                'grade' => $item['grade'],
                'author_email' => $item['author']['email'],
                'author_name' => $item['author']['name'],
                'product' => $item['product']['localId'],
                'body' => $item['body'],
                'public_identifier' => $item['publicIdentifier'],
                'language' => $item['language'],
                'original_body' => $originalBody,
                'order_increment_id' => $item['orderIdentifier'],
                'gtin_code' => $item['product']['gtin'],
                'mpn_code' => $item['product']['mpn']
            ];

            if ($id = $this->reviewModel->checkIfExists($item['publicIdentifier'], $originalBody, $translation)) {
                $data['id'] = $id;
            }

            $productReview->setData($data);
            $this->productReviewResource->save($productReview);

            $reviewData = [
                'trustmate_review_id' => $productReview->getId(),
                'title' => 'Opinia z TrustMate',
                'detail' => $data['body'],
                'nickname' => $data['author_name'],
                'grade' => $data['grade'],
                'sku' => $data['product']
            ];

            $this->reviewModel->saveReviewToMagento($reviewData, $data['language']);
        }
    }
}
