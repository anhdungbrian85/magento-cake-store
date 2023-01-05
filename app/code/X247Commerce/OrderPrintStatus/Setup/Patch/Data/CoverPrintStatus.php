<?php
namespace X247Commerce\OrderPrintStatus\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CoverPrintStatus implements DataPatchInterface
{

    protected $resource;

    private $moduleDataSetup;


    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\App\ResourceConnection $resource,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resource = $resource;
        $this->eavSetupFactory = $eavSetupFactory;
    }


    public function apply()
    { 
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $tableName = $this->resource->getTableName('sales_order');
        $connection = $this->resource->getConnection();
        $connection->update(
            $tableName,
            ["print_status" =>1]
        );  
    }

    public static function getDependencies()
    {
        return [];
    }


    public function getAliases()
    {
        return [];
    }


    public static function getVersion()
    {
        return '1.0.0';
    }
}