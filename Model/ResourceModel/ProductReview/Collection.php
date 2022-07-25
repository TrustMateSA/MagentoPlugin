<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel\ProductReview;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use TrustMate\Opinions\Model\ProductReview;
use TrustMate\Opinions\Model\ResourceModel\ProductReview as ProductReviewResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Init Collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductReview::class, ProductReviewResourceModel::class);
    }
}
