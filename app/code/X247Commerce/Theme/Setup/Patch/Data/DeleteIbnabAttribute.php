<?php

namespace X247Commerce\Theme\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EavCollectionFactory;

class DeleteIbnabAttribute implements DataPatchInterface
{
    private $_moduleDataSetup;

    private $_eavSetupFactory;

    private $eavConfig;

    private $attrOptionCollectionFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        EavCollectionFactory $attrOptionCollectionFactory
    ) {
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
    }

    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_moduleDataSetup]);
        if( $eavSetup->getAttributeId(\Magento\Catalog\Model\Category::ENTITY, 'level_column_count')) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'level_column_count');
        }
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
