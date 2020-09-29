<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 */

namespace TrustMate\Opinions\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ProductOpinions
 * @package TrustMate\Opinions\Model
 */
class ProductOpinions extends AbstractModel
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('TrustMate\Opinions\Model\ResourceModel\ProductOpinions');
    }
}
