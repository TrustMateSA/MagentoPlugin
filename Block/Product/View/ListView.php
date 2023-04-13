<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Block\Product\View;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Review\Block\Product\View\ListView as ProductListView;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\Review;
use TrustMate\Opinions\Api\Data\ProductReviewInterface;

class ListView extends ProductListView
{
    /**
     * @param Review|ProductReviewInterface $review
     *
     * @return bool
     */
    public function isTrustMateOpinion($review): bool
    {
        return $review->getTitle() === 'Opinia z TrustMate';
    }

    /**
     * @inheritdoc
     */
    public function getReviewsCollection(): Collection
    {
        $product = $this->getProduct();

        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewsColFactory->create()
                ->addFieldToFilter('detail.store_id', ['eq' => $this->_storeManager->getStore()->getId()]);
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $products   = $product->getTypeInstance()->getChildrenIds($product->getId());
                $products[] = $product->getId();
                $this->_reviewsCollection->addFieldToFilter('entity_pk_value', ['in' => $products]);
            } else {
                $this->_reviewsCollection->addEntityFilter('product', $product->getId());
            }
        }

        return $this->_reviewsCollection;
    }
}
