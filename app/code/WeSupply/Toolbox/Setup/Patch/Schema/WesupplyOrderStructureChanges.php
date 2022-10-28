<?php
namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class WesupplyOrderStructureChanges implements SchemaPatchInterface, PatchVersionInterface
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

        $this->deleteHistoricalOrders($setup);

        $tableDescribe = $setup->getConnection()->describeTable($setup->getTable('wesupply_orders'));
        if ($tableDescribe['order_id']['DATA_TYPE'] != 'int') {
            $setup->getConnection()->changeColumn(
                $setup->getTable('wesupply_orders'),
                'order_id',
                'order_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Order Id'
                ]
            );
        }
        $setup->getConnection()
            ->addIndex(
                $setup->getTable('wesupply_orders'),
                $setup->getIdxName('wesupply_orders', ['order_id']),
                ['order_id']
            );

        $setup->getConnection()->changeColumn(
            $setup->getTable('wesupply_orders'),
            'order_number',
            'order_number',
            [
                'type'=> \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'length' => 32,
                'default' => '',
                'comment' => 'Order Number'
            ]
        );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable('wesupply_orders'),
                $setup->getIdxName('wesupply_orders', ['order_number']),
                ['order_number']
            );

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.16';
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
            AddReturnsListTable::class
        ];
    }

    /**
     * @param $setup
     */
    private function deleteHistoricalOrders($setup)
    {
        $endDate = date('Y-m-d H:i:s', strtotime('-1 day', time()));

        $conn = $setup->getConnection();
        $tableName = $conn->getTableName($setup->getTable('wesupply_orders'));

        $whereConditions = [
            $conn->quoteInto('updated_at < ?', $endDate)
        ];

        $conn->delete($tableName, $whereConditions);
    }
}
