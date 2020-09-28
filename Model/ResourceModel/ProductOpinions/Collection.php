<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\ResourceModel\ProductOpinions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package TrustMate\Opinions\Model\ResourceModel\ProductOpinions
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('TrustMate\Opinions\Model\ProductOpinions', 'TrustMate\Opinions\Model\ResourceModel\ProductOpinions');
    }
}
