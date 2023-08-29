<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2022 TrustMate
 */

declare(strict_types=1);

namespace TrustMate\Opinions\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductReview extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('trustmate_product_opinions', 'id');
    }

    /**
     * @return Select
     */
    public function getTranslation(): Select
    {
        return $this->_getLoadSelect()
            ->join(
                ['translation' => 'trustmate_translated_opinions'],
                'trustmate_product_opinions.id=translation.opinion_id',
                [
                    'lang' => 'translation.language',
                    'trans_body' => 'translation.body'
                ]
            );
    }
}
