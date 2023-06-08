<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;
use TrustMate\Opinions\Api\Data\ProductReviewSearchResultInterface;
use TrustMate\Opinions\Api\Data\ProductReviewSearchResultInterfaceFactory;
use TrustMate\Opinions\Api\ProductReviewRepositoryInterface;
use TrustMate\Opinions\Model\ProductReviewFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductReview as ProductReviewResourceModel;
use TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory as ProductReviewCollectionFactory;

class ProductReviewRepository implements ProductReviewRepositoryInterface
{
    /**
     * @var ProductReviewFactory
     */
    private $productReviewFactory;

    /**
     * @var ProductReviewResourceModel
     */
    private $productReviewResourceModel;

    /**
     * @var ProductReviewCollectionFactory
     */
    private $productReviewCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ProductReviewSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @param ProductReviewFactory $productReviewFactory
     * @param ProductReviewResourceModel $productReviewResourceModel
     * @param ProductReviewCollectionFactory $productReviewCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductReviewSearchResultInterfaceFactory $searchResultFactory
     */
    public function __construct(
        ProductReviewFactory                      $productReviewFactory,
        ProductReviewResourceModel                $productReviewResourceModel,
        ProductReviewCollectionFactory            $productReviewCollectionFactory,
        CollectionProcessorInterface              $collectionProcessor,
        ProductReviewSearchResultInterfaceFactory $searchResultFactory
    ) {
        $this->productReviewFactory = $productReviewFactory;
        $this->productReviewResourceModel = $productReviewResourceModel;
        $this->productReviewCollectionFactory = $productReviewCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $id
     *
     * @return ProductReviewInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): ProductReviewInterface
    {
        $productReview = $this->productReviewFactory->create();
        $this->productReviewResourceModel->load($productReview, $id, 'id');

        if (!$productReview->getId()) {
            throw new NoSuchEntityException(__('Unable to find Product Review with ID "%1"', $id));
        }

        return $productReview;
    }

    /**
     * @param ProductReviewInterface $productReview
     *
     * @return ProductReviewInterface
     * @throws AlreadyExistsException
     */
    public function save(ProductReviewInterface $productReview): ProductReviewInterface
    {
        $this->productReviewResourceModel->save($productReview);

        return $productReview;
    }

    /**
     * @param ProductReviewInterface $productReview
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProductReviewInterface $productReview): bool
    {
        try {
            $this->productReviewResourceModel->delete($productReview);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the entry: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $productReviewCollection = $this->productReviewCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $productReviewCollection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($productReviewCollection->getItems());

        return $searchResults;
    }
}
