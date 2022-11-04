<?php
namespace WeSupply\Toolbox\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class AddOrderAdditionalIndexes implements SchemaPatchInterface, PatchVersionInterface
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
        $setup->getConnection()
            ->dropIndex(
                $setup->getTable('wesupply_orders'),
                $setup->getIdxName('wesupply_orders', ['store_id'])
            );
        $setup->getConnection()
            ->addIndex(
                $setup->getTable('wesupply_orders'),
                $setup->getIdxName('wesupply_orders', ['store_id', 'updated_at', 'is_excluded']),
                ['store_id', 'updated_at', 'is_excluded']
            );

        $this->schemaSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.19';
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
            AddOrderIndexes::class
        ];
    }
}
