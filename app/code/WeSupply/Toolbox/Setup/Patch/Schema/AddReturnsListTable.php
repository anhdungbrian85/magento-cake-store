<?php
namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddReturnsListTable implements SchemaPatchInterface, PatchVersionInterface
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
        $setup = $this->schemaSetup;
        $this->schemaSetup->startSetup();

        $setup->getConnection()->dropTable(
            $setup->getTable('wesupply_returns_list')
        );

        $table = $setup->getConnection()
            ->newTable($setup->getTable('wesupply_returns_list'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'primary' => true,
                    'nullable' => false,
                    'unsigned' => true,
                    'comment' => 'Id'
                ]
            )->addColumn(
                'return_reference',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true,
                    'comment' => 'Return Reference ID'
                ]
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Return Status'
                ]
            )->addColumn(
                'refunded',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => false,
                    'comment' => 'Refund Status'
                ]
            )->addColumn(
                'creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'CreditMemo Increment ID'
                ]
            )->addColumn(
                'request_log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Request Log ID'
                ]
            );

        $setup->getConnection()->createTable($table);

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.15';
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
        return [
            AddAwaitingUpdateCompleteColumn::class
        ];
    }
}
