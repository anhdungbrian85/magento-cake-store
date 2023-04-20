<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Ulmod\Productinquiry\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class InquiryAttributesSetup implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $this->createInquiryAttributesSetup($eavSetup);
    }

   /**
    * Create inquiry product attributes
    *
    * @param \Magento\Eav\Setup\EavSetup $eavSetup EAV module Setup
    */
    public function createInquiryAttributesSetup($eavSetup)
    {
        $eavSetup->removeAttribute(ProductModel::ENTITY, 'um_productinquiry');
        $eavSetup->removeAttribute(ProductModel::ENTITY, 'um_productinquiry_text');
            
        // Add inquiry attributes
        $eavSetup->addAttribute(
            ProductModel::ENTITY,
            'um_productinquiry',
            [
                'group' => 'Product Inquiry (by Ulmod)',
                'type' => 'int',
                'label' => 'Enable Inquiry Button For This Product',
                'note'  => 'If Yes, the inquiry button will shown for this product.',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'default' => 0,
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'filterable' => false,
                'searchable'  => false,
                'comparable' => false,
                'visible_in_advanced_search' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => false,
                'unique' => false,
                'is_filterable_in_grid' => false,
                'is_visible_in_grid' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped'
            ]
        );
        
        $eavSetup->addAttribute(
            ProductModel::ENTITY,
            'um_productinquiry_text',
            [
                'group' => 'Product Inquiry (by Ulmod)',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Inquiry Button Text',
                'note'  => 'Enter the text to appear at the inquiry button. Eg. Inquiry',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple,configurable,virtual,bundle,downloadable,grouped',
                'system' => false
            ]
        );
    }
}
