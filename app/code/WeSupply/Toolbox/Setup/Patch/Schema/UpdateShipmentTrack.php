<?php
namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateShipmentTrack implements SchemaPatchInterface, PatchVersionInterface
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

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_shipment_track'),
            'wesupply_order_update',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => true,
                'unsigned' => true,
                'default' => 0,
                'after'    => 'updated_at',
                'comment' => 'Wesupply Order Update Flag'
            ]
        );

        $setup->getConnection()
            ->addIndex(
                $setup->getTable('sales_shipment_track'),
                $setup->getIdxName('sales_shipment_track', ['wesupply_order_update']),
                ['wesupply_order_update']
            );

        $endDate = date('Y-m-d H:i:s', strtotime('-1 day', time()));
        $shipmentTrackTableName = $setup->getTable('sales_shipment_track');
        $setup->getConnection()->query( "UPDATE " . $shipmentTrackTableName . " SET `wesupply_order_update` = 1 WHERE (created_at <= '" . $endDate . "')");

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.17';
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
            WesupplyOrderStructureChanges::class
        ];
    }
}
