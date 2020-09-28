<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductOpinions
 * @package TrustMate\Opinions\Model\ResourceModel
 */
class ProductOpinions extends AbstractDb
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('trustmate_product_opinions', 'id');
    }
}
