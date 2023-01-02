<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\ResourceModel\Review as SourceReviewResource;
use Magento\Review\Model\Review as MagentoReview;
use Magento\Review\Model\ReviewFactory;
use TrustMate\Opinions\Api\ProductReviewRepositoryInterface;
use TrustMate\Opinions\Enum\ReviewDataEnum;
use TrustMate\Opinions\Enum\TrustMateDataEnum;
use TrustMate\Opinions\Model\Option as TrustMateOption;
use TrustMate\Opinions\Model\Rating as TrustMateRating;

class Review
{
    /**
     * @var ProductReviewRepositoryInterface
     */
    private $productReviewRepositoryInterface;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var FilterGroup
     */
    private $filterGroup;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteriaInterface;

    /**
     * @var SortOrder
     */
    private $sortOrder;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var SourceReviewResource
     */
    private $reviewResource;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var Rating
     */
    private $rating;

    /**
     * @var Option
     */
    private $option;

    /**
     * @param ProductReviewRepositoryInterface $productReviewRepositoryInterface
     * @param Filter                           $filter
     * @param FilterGroup                      $filterGroup
     * @param SearchCriteriaInterface          $searchCriteriaInterface
     * @param SortOrder                        $sortOrder
     * @param Store                            $store
     * @param TrustMateRating                  $rating
     * @param TrustMateOption                  $option
     * @param ReviewFactory                    $reviewFactory
     * @param SourceReviewResource             $reviewResource
     */
    public function __construct(
        ProductReviewRepositoryInterface $productReviewRepositoryInterface,
        Filter                           $filter,
        FilterGroup                      $filterGroup,
        SearchCriteriaInterface          $searchCriteriaInterface,
        SortOrder                        $sortOrder,
        Store                            $store,
        Rating                           $rating,
        Option                           $option,
        ReviewFactory                    $reviewFactory,
        SourceReviewResource             $reviewResource
    ) {
        $this->productReviewRepositoryInterface = $productReviewRepositoryInterface;
        $this->filter                           = $filter;
        $this->filterGroup                      = $filterGroup;
        $this->searchCriteriaInterface          = $searchCriteriaInterface;
        $this->sortOrder                        = $sortOrder;
        $this->store                            = $store;
        $this->rating                           = $rating;
        $this->option                           = $option;
        $this->reviewFactory                    = $reviewFactory;
        $this->reviewResource                   = $reviewResource;
    }

    /**
     * Get latest review updated date
     *
     * @param bool $translation
     *
     * @return null|string
     * @throws InputException
     * @throws LocalizedException
     */
    public function getLatestUpdatedDate(bool $translation = false): ?string
    {
        $updatedAtOrder = $this->sortOrder
            ->setField('updated_at')
            ->setDirection(SortOrder::SORT_DESC);
        $nullCondition  = $this->filter
            ->setField('original_body')
            ->setConditionType('null')
            ->setValue(1);

        $filterGroup = $this->filterGroup;
        if ($translation) {
            $nullCondition = $this->filter
                ->setField('original_body')
                ->setConditionType('notnull')
                ->setValue(1);
        }

        $filterGroup->setFilters([$nullCondition]);
        $searchCriteria = $this->searchCriteriaInterface
            ->setFilterGroups([$this->filterGroup])
            ->setPageSize(1)
            ->setSortOrders([$updatedAtOrder]);

        $review = $this->productReviewRepositoryInterface->getList($searchCriteria)->getItems();

        return empty($review) ? '1990-01-01 12:00:00' : reset($review)->getUpdatedAt();
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
        $publicIdentifierFilter = $this->filter
            ->setField(TrustMateDataEnum::PUBLIC_IDENTIFIER_COLUMN)
            ->setConditionType(TrustMateDataEnum::CONDITION_EQUAL)
            ->setValue($publicIdentifier);

        $publicIdentifierFilterGroup = $this->filterGroup->setFilters([$publicIdentifierFilter]);
        $originalBodyFilterGroup     = $this->filterGroup;
        if ($translation) {
            $notNullCondition = $this->filter
                ->setField('original_body')
                ->setConditionType(TrustMateDataEnum::CONDITION_EQUAL)
                ->setValue($originalBody);

            $originalBodyFilterGroup->setFilters([$notNullCondition]);
        }

        $searchCriteria = $this->searchCriteriaInterface
            ->setFilterGroups([$publicIdentifierFilterGroup, $originalBodyFilterGroup]);
        $review         = $this->productReviewRepositoryInterface->getList($searchCriteria)->getItems();

        return empty($review)
            ? null
            : reset($review)->getId();
    }

    /**
     * Save review as magento review
     *
     * @param $reviewData
     * @param $language
     *
     * @return void
     * @throws AlreadyExistsException|NoSuchEntityException
     */
    public function saveReviewToMagento($reviewData, $language)
    {
        $storesLocale = $this->store->getStoreLocales();
        $reviewModel  = $this->reviewFactory->create();

        $reviewData['entity_id'] = $reviewModel->getEntityIdByCode(MagentoReview::ENTITY_PRODUCT_CODE);
        $reviewData['status_id'] = MagentoReview::STATUS_APPROVED;
        $reviewData['store_id']  = $storesLocale[$language];
        $reviewData['stores']    = $storesLocale[$language];

        $this->reviewResource->load(
            $reviewModel,
            $reviewData[ReviewDataEnum::TRUSTMATE_REVIEW_ID_COLUMN],
            ReviewDataEnum::TRUSTMATE_REVIEW_ID_COLUMN
        );

        $reviewModel->setData(array_merge($reviewModel->getData(), $reviewData));
        $this->reviewResource->save($reviewModel);

        if ($reviewModel->getId()) {
            $trustMateRating = $this->rating->getRatingByCode('TrustMate');
            $optionData      = $this->option->getOptionByRatingIdAndValue(
                $trustMateRating['rating_id'],
                $reviewData['grade']
            );

            $this->rating->saveRating(
                $trustMateRating['rating_id'],
                $optionData['option_id'],
                $reviewData['entity_pk_value'],
                $reviewModel->getId()
            );

            $reviewModel->aggregate();
        }
    }
}
