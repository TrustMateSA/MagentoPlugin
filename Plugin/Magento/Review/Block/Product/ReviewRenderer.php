<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review\Block\Product;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Block\Product\ReviewRenderer as MagentoReviewRenderer;
use Magento\Store\Model\StoreManagerInterface;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating\CollectionFactory;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating\Collection;

class ReviewRenderer
{
    /**
     * @var CollectionFactory
     */
    private $trustmateReviewRatingCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $trustmateRatingCollection;

    public function __construct(
        CollectionFactory $trustmateRatingCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->trustmateRatingCollection = $trustmateRatingCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param MagentoReviewRenderer $subject
     * @param int|null $result
     *
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function afterGetReviewsCount(MagentoReviewRenderer $subject, ?int $result): ?int
    {
        $this->getTrustmateRatingCollection((int)$subject->getProduct()->getId());

        return $result + $this->trustmateReviewRatingCollection->getFirstItem()->getReviewSum();
    }

    /**
     * @param MagentoReviewRenderer $subject
     * @param int|null $result
     *
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function afterGetRatingSummary(MagentoReviewRenderer $subject, ?int $result): ?int
    {
        if (!$this->trustmateReviewRatingCollection) {
            $this->getTrustmateRatingCollection((int)$subject->getProduct()->getId());
        }

        return (!$this->trustmateReviewRatingCollection->getFirstItem()->getPercent())
            ? (int)$result
            : (int)(($result + $this->trustmateReviewRatingCollection->getFirstItem()->getPercent()) / 2);
    }

    /**
     * @param int $productId
     * @return void
     * @throws NoSuchEntityException
     */
    private function getTrustmateRatingCollection(int $productId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->trustmateReviewRatingCollection = $this->trustmateRatingCollection->create()
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->addFieldToFilter('product_id', ['eq' => $productId]);
    }
}
