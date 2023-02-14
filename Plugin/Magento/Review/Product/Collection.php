<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review\Product;

use function PHPUnit\Framework\isInstanceOf;

class Collection
{
    private $cachedItemReviewIds = [];

    public function aroundAddItem($product, $proceed, $item)
    {
        if (in_array($item->getData('review_id'), $this->cachedItemReviewIds, true)) {
            $newReviewId = $item->getData('review_id') . '_' . $item->getData('store_id');
            $item->setData('review_id', $newReviewId);

            return $proceed($item);
        }

        $this->cachedItemReviewIds[] = $item->getData('review_id');

        return $proceed($item);
    }
}
