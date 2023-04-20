<?php
namespace WeltPixel\Quickview\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddMessagesTable implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface $schemaSetup
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $installer = $this->schemaSetup;
        $this->schemaSetup->startSetup();

        /**
         * Create table 'weltpixel_quickviewmessages'
         */
        $quickViewMessagesTableName = $installer->getTable('weltpixel_quickviewmessages');

        if ($installer->getConnection()->isTableExists($quickViewMessagesTableName)  != true ) {
            $table = $installer->getConnection()->newTable($quickViewMessagesTableName)
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Title'
                )->addColumn(
                    'priority',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Status'
                )->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Status'
                )->addColumn(
                    'valid_from',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => NULL],
                    'Valid From'
                )->addColumn(
                    'valid_to',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['unsigned' => true, 'nullable' => true, 'default' => NULL],
                    'Valid To'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Store id'
                )->addColumn(
                    'customer_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Customer Group'
                )->addColumn(
                    'conditions_serialized',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true, 'default' => ''],
                    'Conditions'
                )->addColumn(
                    'custom_message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Custom Message'
                )->addColumn(
                    'custom_message_bg_color',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Custom Message Background Color'
                )->addColumn(
                    'custom_message_font_color',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Custom Message Font Color'
                )->addColumn(
                    'custom_message_font_size',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    32,
                    [],
                    'Custom Message Font Size'
                )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Created Time'
                )->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                    'Update Time'
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('weltpixel_quickviewmessages'),
                        ['title', 'custom_message'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['title', 'custom_message'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )->setComment(
                    'WeltPixel Quickview Messages'
                );

            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'weltpixel_quickviewmessages_rule_idx'
         */
        $quickViewMessagesRuleTableName = $installer->getTable('weltpixel_quickviewmessages_rule_idx');
        if ($installer->getConnection()->isTableExists($quickViewMessagesRuleTableName)  != true ) {
            $table = $installer->getConnection()->newTable($quickViewMessagesRuleTableName)
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Rule Id'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product Id'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store id'
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('weltpixel_quickviewmessages_rule_idx'),
                        ['rule_id']
                    ),
                    ['rule_id']
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('weltpixel_quickviewmessages_rule_idx'),
                        ['product_id']
                    ),
                    ['product_id']
                )->addIndex(
                    $installer->getIdxName(
                        $installer->getTable('weltpixel_quickviewmessages_rule_idx'),
                        ['store_id']
                    ),
                    ['store_id']
                )->setComment(
                    'WeltPixel Quickview Custom Messages Rules Index'
                );

            $installer->getConnection()->createTable($table);
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.2';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
