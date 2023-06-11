<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel\ProductReviewRating;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use TrustMate\Opinions\Model\ProductReviewRating;
use TrustMate\Opinions\Model\ResourceModel\ProductReviewRating as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductReviewRating::class, ResourceModel::class);
    }
}
