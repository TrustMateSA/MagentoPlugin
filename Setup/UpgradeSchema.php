<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2020 TrustMate
 * @since     1.1.0
 */

namespace TrustMate\Opinions\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeSchema
 *
 * @package TrustMate\Opinions\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<=')) {
            $salesOrderTable = $setup->getTable('sales_order');

            $setup->getConnection()
                ->addColumn(
                    $salesOrderTable,
                    'trustmate_agreement',
                    [
                        'type'    => Table::TYPE_BOOLEAN,
                        'length'  => null,
                        'comment' => 'Order has TrustMate agreement'
                    ]
                );
        }

        $setup->endSetup();
    }
}