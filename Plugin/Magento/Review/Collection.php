<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Plugin\Magento\Review;

class Collection
{
    public function aroundAddItem($product, $proceed, $item)
    {
        $allIds = $product->getAllIds();
        $countIds = array_count_values($allIds);
        if (isset($countIds[$item->getReviewId()]) && $countIds[$item->getReviewId()] > 1) {
            $newReviewId = $item->getData('review_id') . '_' . $item->getData('store_id');
            $item->setData('review_id', $newReviewId);

            return $proceed($item);
        }

        return $proceed($item);
    }
}
