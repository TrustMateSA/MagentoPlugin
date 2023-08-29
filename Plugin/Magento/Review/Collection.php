<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Review\Model\ResourceModel\Review\Collection as MagentoReviewCollection;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Model\ResourceModel\ProductReview\CollectionFactory as TrustmateCollectionFactory;
use TrustMate\Opinions\Model\Review as TrustmateReviewModel;

class Collection
{
    /**
     * @var TrustmateCollectionFactory
     */
    private $trustmateCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var TrustmateReviewModel
     */
    private $reviewModel;

    /**
     * @param TrustmateCollectionFactory $trustmateCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param TrustmateReviewModel $reviewModel
     */
    public function __construct(
        TrustmateCollectionFactory $trustmateCollectionFactory,
        StoreManagerInterface      $storeManager,
        Registry                   $registry,
        TrustmateReviewModel       $reviewModel
    ) {
        $this->trustmateCollectionFactory = $trustmateCollectionFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->reviewModel = $reviewModel;
    }

    /**
     * @param MagentoReviewCollection $subject
     * @param array $result
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function afterGetItems(MagentoReviewCollection $subject, array $result): array
    {
        foreach ($result as $key => $review) {
            if (in_array($review->getEntityCode(), ['product', 'customer', 'category'])) {
                continue;
            }

            $trustmateReview = $this->reviewModel->getTrustmateReview((int)$review->getDetailId());
            $review->setData('title', 'Opinia z TrustMate');
            $review->setData('language', $trustmateReview->getLanguage());
            $review->setData(
                'percent',
                ((int)$trustmateReview->getGrade() / 5) * 100
            );
        }

        return $result;
    }
}
