<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use TrustMate\Opinions\Model\ProductReviewRatingFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating as ProductReviewRatingResourceModel;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating\Collection;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating\CollectionFactory as ProductReviewRatingCollectionFactory;

class Rating
{
    /**
     * @var ProductReviewRatingFactory
     */
    private $ratingModelFactory;

    /**
     * @var ProductReviewRatingResourceModel
     */
    private $ratingResourceModel;

    /**
     * @var ProductReviewRatingCollectionFactory
     */
    private $productReviewRatingCollection;

    public function __construct(
        ProductReviewRatingFactory $ratingModelFactory,
        ProductReviewRatingResourceModel $ratingResourceModel,
        ProductReviewRatingCollectionFactory $productReviewRatingCollection
    ) {
        $this->ratingModelFactory = $ratingModelFactory;
        $this->ratingResourceModel = $ratingResourceModel;
        $this->productReviewRatingCollection = $productReviewRatingCollection;
    }

    /**
     * @param $storeId
     * @param $productId
     * @param $grade
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function save($storeId, $productId, $grade)
    {
        $rating = $this->ratingModelFactory->create();
        $reviewRating = $this->checkIfExists($storeId, $productId);
        $reviewRatingId = $reviewRating->getId();
        if ($reviewRatingId) {
            $rating->setData('id', $reviewRatingId);
        }

        $reviewSum = ($reviewRatingId) ? (int)$reviewRating->getReviewSum() + 1 : 1;
        $percent = ($reviewRatingId)
            ? ((int)$reviewRating->getPercent() + (($grade / 5) * 100)) / 2
            : ($grade / 5) * 100;
        $rating->setData('review_sum', $reviewSum);
        $rating->setData('percent', $percent);
        $rating->setData('store_id', $storeId);
        $rating->setData('product_id', $productId);

        $this->ratingResourceModel->save($rating);
    }

    /**
     * @param $storeId
     * @param $productId
     *
     * @return DataObject
     */
    private function checkIfExists($storeId, $productId): DataObject
    {
        /** @var Collection $ratingCollection */
        $ratingCollection = $this->productReviewRatingCollection->create()
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->addFieldToFilter('product_id', ['eq' => $productId]);

        return $ratingCollection->getFirstItem();
    }
}
