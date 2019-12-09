<?php
/**
 * @package   TrustMate\Opinions
 * @copyright 2019 TrustMate
 */

namespace TrustMate\Opinions\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    const PRODUCT_OPINIONS_TABLE = 'trustmate_product_opinions';

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createProductOpinionsTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    private function createProductOpinionsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(self::PRODUCT_OPINIONS_TABLE))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Created at'
            )
            ->addColumn(
                'grade',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Grade'
            )
            ->addColumn(
                'author_email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Email'
            )
            ->addColumn(
                'author_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'product',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product SKU'
            )
            ->addColumn(
                'body',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Opinion text'
            )
            ->setComment('TrustMate Product Opinions Table');

        $setup->getConnection()->createTable($table);
    }
}
