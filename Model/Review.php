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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\ResourceModel\Review as SourceReviewResource;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
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
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var SourceReviewResource
     */
    private $reviewResource;

    /**
     * @var Rating
     */
    private $rating;

    /**
     * @var Option
     */
    private $option;

    /**
     * @var CollectionFactory
     */
    private $reviewCollection;

    /**
     * @param ProductReviewRepositoryInterface $productReviewRepositoryInterface
     * @param FilterBuilder                    $filterBuilder
     * @param FilterGroupBuilder               $filterGroupBuilder
     * @param SearchCriteriaBuilder            $searchCriteriaBuilder
     * @param SortOrder                        $sortOrder
     * @param TrustMateRating                  $rating
     * @param TrustMateOption                  $option
     * @param ReviewFactory                    $reviewFactory
     * @param SourceReviewResource             $reviewResource
     * @param CollectionFactory                $reviewCollection
     */
    public function __construct(
        ProductReviewRepositoryInterface $productReviewRepositoryInterface,
        FilterBuilder                    $filterBuilder,
        FilterGroupBuilder               $filterGroupBuilder,
        SearchCriteriaBuilder            $searchCriteriaBuilder,
        SortOrder                        $sortOrder,
        Rating                           $rating,
        Option                           $option,
        ReviewFactory                    $reviewFactory,
        SourceReviewResource             $reviewResource,
        Collectionfactory                $reviewCollection
    ) {
        $this->productReviewRepositoryInterface = $productReviewRepositoryInterface;
        $this->filterBuilder                    = $filterBuilder;
        $this->filterGroupBuilder               = $filterGroupBuilder;
        $this->searchCriteriaBuilder            = $searchCriteriaBuilder;
        $this->sortOrder                        = $sortOrder;
        $this->rating                           = $rating;
        $this->option                           = $option;
        $this->reviewFactory                    = $reviewFactory;
        $this->reviewResource                   = $reviewResource;
        $this->reviewCollection                 = $reviewCollection;
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
        $nullCondition  = $this->filterBuilder
            ->setField('original_body')
            ->setConditionType('null')
            ->setValue(1)
            ->create();

        if ($translation) {
            $nullCondition->setConditionType('notnull')
                ->setValue(1);
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters([$nullCondition])
            ->setPageSize(1)
            ->setSortOrders([$updatedAtOrder])
            ->create();

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
        $publicIdentifierFilter = $this->filterBuilder
            ->setField(TrustMateDataEnum::PUBLIC_IDENTIFIER_COLUMN)
            ->setConditionType(TrustMateDataEnum::CONDITION_EQUAL)
            ->setValue($publicIdentifier)
            ->create();
        $publicIdentifierFilterGroup = $this->filterGroupBuilder
            ->addFilter($publicIdentifierFilter)
            ->create();

        if ($translation) {
            $originalBodyFilter = $this->filterBuilder
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

    /**
     * Save review as magento review
     *
     * @param array      $data
     * @param string|int $store
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function saveReviewToMagento(array $data, $store)
    {
        $reviewModel = $this->reviewFactory->create();

        $data['entity_id'] = $reviewModel->getEntityIdByCode(MagentoReview::ENTITY_PRODUCT_CODE);
        $data['status_id'] = MagentoReview::STATUS_APPROVED;
        $data['store_id']  = $store;
        $data['stores']    = $store;

        $reviewId = $this->reviewCollection->create()
            ->addFieldToFilter(ReviewDataEnum::TRUSTMATE_REVIEW_ID_COLUMN, ['eq' => $data[ReviewDataEnum::TRUSTMATE_REVIEW_ID_COLUMN]])
            ->addFieldToFilter(ReviewDataEnum::REVIEW_STORE_ID_COLUMN, ['eq' => $store])
            ->getFirstItem()
            ->getReviewId();

        $data['review_id'] = $reviewId;
        $reviewModel->setData($data);
        $this->reviewResource->save($reviewModel);

        if ($reviewModel->getId()) {
            $trustMateRating = $this->rating->getRatingByCode('TrustMate');
            $optionData      = $this->option->getOptionByRatingIdAndValue(
                $trustMateRating['rating_id'],
                $data['grade']
            );

            $this->rating->saveRating(
                $trustMateRating['rating_id'],
                $optionData['option_id'],
                $data['entity_pk_value'],
                $reviewModel->getId()
            );

            $reviewModel->aggregate();
        }
    }
}
