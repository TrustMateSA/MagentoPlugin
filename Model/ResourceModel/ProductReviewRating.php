<?php

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductReviewRating extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('trustmate_product_opinions_rating', 'id');
    }
}
