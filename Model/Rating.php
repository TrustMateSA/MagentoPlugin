<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating as RatingResource;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory;

class Rating
{
    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var RatingResource
     */
    private $ratingResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param RatingFactory $ratingFactory
     * @param RatingResource $ratingResource
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        RatingFactory $ratingFactory,
        RatingResource $ratingResource,
        CollectionFactory $collectionFactory
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->ratingResource = $ratingResource;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Save rating
     *
     * @param $ratingId
     * @param $optionId
     * @param $productId
     * @param $reviewId
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function saveRating($ratingId, $optionId, $productId, $reviewId): void
    {
        $ratingModel = $this->ratingFactory->create()
            ->setRatingId($ratingId)
            ->setReviewId($reviewId)
            ->addOptionVote($optionId, $productId);

        $this->ratingResource->save($ratingModel);
    }

    /**
     * Get rating by code
     *
     * @param string $code
     *
     * @return array|mixed|null
     */
    public function getRatingByCode(string $code)
    {
        return $this->collectionFactory->create()
            ->addFieldToFilter('rating_code', 'TrustMate')
            ->getFirstItem()
            ->getData();
    }
}
