<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;
use TrustMate\Opinions\Api\Data\ProductReviewSearchResultInterface;

interface ProductReviewRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return ProductReviewInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): ProductReviewInterface;

    /**
     * @param ProductReviewInterface $productReview
     *
     * @return ProductReviewInterface
     * @throws CouldNotSaveException
     */
    public function save(ProductReviewInterface $productReview): ProductReviewInterface;

    /**
     * @param ProductReviewInterface $productReview
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductReviewInterface $productReview): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return ProductReviewSearchResultInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
