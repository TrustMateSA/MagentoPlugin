<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use TrustMate\Opinions\Api\ProductReviewRepositoryInterface;
use TrustMate\Opinions\Enum\TrustMateDataEnum;

class Review
{
    /**
     * @var ProductReviewRepositoryInterface
     */
    private $productReviewRepositoryInterface;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrder
     */
    private $sortOrder;

    /**
     * @param ProductReviewRepositoryInterface $productReviewRepositoryInterface
     * @param FilterBuilder                    $filterBuilder
     * @param FilterGroupBuilder               $filterGroupBuilder
     * @param SearchCriteriaBuilder            $searchCriteriaBuilder
     * @param SortOrder                        $sortOrder
     */
    public function __construct(
        ProductReviewRepositoryInterface $productReviewRepositoryInterface,
        FilterBuilder                    $filterBuilder,
        FilterGroupBuilder               $filterGroupBuilder,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
        SortOrder                        $sortOrder
    ) {
        $this->productReviewRepositoryInterface = $productReviewRepositoryInterface;
        $this->filterBuilder                    = $filterBuilder;
        $this->filterGroupBuilder               = $filterGroupBuilder;
        $this->searchCriteriaBuilder            = $searchCriteriaBuilder;
        $this->sortOrder                        = $sortOrder;
    }

    /**
     * Get latest review updated date
     *
     * @param int  $storeId
     * @param bool $translation
     *
     * @return null|string
     * @throws InputException
     * @throws LocalizedException
     */
    public function getLatestUpdatedDate(int $storeId, bool $translation = false): ?string
    {
        $updatedAtOrder   = $this->sortOrder
            ->setField('updated_at')
            ->setDirection(SortOrder::SORT_DESC);
        $storeIdCondition = $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('eq')
            ->setValue($storeId)
            ->create();
        $storeIdFilterGroup = $this->filterGroupBuilder
            ->addFilter($storeIdCondition)
            ->create();

        if ($translation) {
            $notNullCondition        = $this->filterBuilder
                ->setField('original_body')
                ->setConditionType('notnull')
                ->setValue(1)
                ->create();
            $translationsFilterGroup = $this->filterGroupBuilder
                ->addFilter($notNullCondition)
                ->create();
        } else {
            $nullCondition                  = $this->filterBuilder
                ->setField('original_body')
                ->setConditionType('null')
                ->setValue(0)
                ->create();
            $withoutTranslationsFilterGroup = $this->filterGroupBuilder
                ->addFilter($nullCondition)
                ->create();
        }

        $filtersGroup = isset($nullCondition) ? $withoutTranslationsFilterGroup : $translationsFilterGroup;

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups([$filtersGroup, $storeIdFilterGroup])
            ->setPageSize(1)
            ->setSortOrders([$updatedAtOrder])
            ->create();

        $productReviews = $this->productReviewRepositoryInterface->getList($searchCriteria);
        $reviewList = $productReviews->getItems();

        return empty($reviewList) ? '1990-01-01 12:00:00' : reset($reviewList)->getUpdatedAt();
    }

    /**
     * Check if review exists and return id
     *
     * @param string      $publicIdentifier
     * @param string|null $originalBody
     * @param bool        $translation
     *
     * @return null|string
     * @throws LocalizedException
     */
    public function checkIfExists(string $publicIdentifier, ?string $originalBody, bool $translation = false): ?string
    {
        $publicIdentifierFilter      = $this->filterBuilder
            ->setField(TrustMateDataEnum::PUBLIC_IDENTIFIER_COLUMN)
            ->setConditionType(TrustMateDataEnum::CONDITION_EQUAL)
            ->setValue($publicIdentifier)
            ->create();
        $publicIdentifierFilterGroup = $this->filterGroupBuilder
            ->addFilter($publicIdentifierFilter)
            ->create();

        if ($translation) {
            $originalBodyFilter      = $this->filterBuilder
                ->setField('original_body')
                ->setConditionType(TrustMateDataEnum::CONDITION_EQUAL)
                ->setValue($originalBody)
                ->create();
            $originalBodyFilterGroup = $this->filterGroupBuilder
                ->addFilter($originalBodyFilter)
                ->create();
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups(
                isset($originalBodyFilter)
                    ? [$publicIdentifierFilterGroup, $originalBodyFilterGroup]
                    : [$publicIdentifierFilterGroup]
            )->create();
        $review         = $this->productReviewRepositoryInterface->getList($searchCriteria)->getItems();

        return empty($review)
            ? null
            : reset($review)->getId();
    }
}
