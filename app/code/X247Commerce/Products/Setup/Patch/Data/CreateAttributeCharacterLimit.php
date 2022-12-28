<?php

namespace X247Commerce\Products\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EavCollectionFactory;

class CreateAttributeCharacterLimit implements DataPatchInterface
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
        if( !$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'character_limit')) {
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'character_limit', [
               'type' => 'int',
               'label' => 'Character Limit',
               'input' => 'text',
               'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
               'visible' => true,
               'required' => false,
               'user_defined' => true,
               'default' => '',
               'searchable' => false,
               'filterable' => false,
               'comparable' => false,
               'visible_on_front' => false,
               'used_in_product_listing' => true,
               'group' => 'General',
               'is_used_in_grid' => true,
               'visible_in_advanced_search' => true
           ]);
        }

        $this->_moduleDataSetup->getConnection()->endSetup();
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
