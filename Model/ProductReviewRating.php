<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model;

use Magento\Framework\Model\AbstractModel;

class ProductReviewRating extends AbstractModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\ProductReviewRating::class);
    }
}
