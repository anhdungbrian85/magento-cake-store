<?php

namespace X247Commerce\ChangeOrderStatus\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\ResourceConnection;

class ChangeAutoCompleteFlagValue implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $resourceConnection;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resourceConnection = $resourceConnection;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $statuses = array( 'pending', 'processing' );
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $data = ["auto_complete_flag" => 0];
        $where = ['status in (?)' => $statuses];
        $connection->update($tableName, $data, $where);

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}