<?php
namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddAwaitingUpdateCompleteColumn implements SchemaPatchInterface, PatchVersionInterface
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
            $setup->getTable('wesupply_orders'),
            'awaiting_update',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                'nullable' => false,
                'unsigned' => true,
                'default' => false,
                'after'    => 'store_id',
                'comment' => 'Order was updated by ERP or other'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('wesupply_orders'),
            'updated_at',
            'updated_at',
            [
                'type'=> \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                'nullable' => false,
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                'comment' => 'Updated At'
            ]
        );

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.13';
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
            AddExcludeImportCompleteColumn::class
        ];
    }
}
