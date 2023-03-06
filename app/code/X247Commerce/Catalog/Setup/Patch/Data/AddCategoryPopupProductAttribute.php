<?php

declare (strict_types = 1);

namespace X247Commerce\Catalog\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCategoryPopupProductAttribute implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * CollectionFactory
     *
     * @var categoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply() 
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'category_show_in_popup_crossell',
            [
            'type' => 'text',
            'backend' => '',
            'frontend' => '',
            'label' => 'Category show in Popup Crossell',
            'input' => 'multiselect',
            'class' => '',
            'source' => 'X247Commerce\Catalog\Model\Config\Product\CategoryArray',
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => $this->getDefaultCategory(),
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'group' => 'General',
            'is_used_in_grid' => true,
            'visible_in_advanced_search' => true
        ]);
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'category_show_in_popup_crossell');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    public function getDefaultCategory()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categories = $categoryCollection->addAttributeToFilter('name', ['in', ['Candles','Balloons']]);
        $categoryIds = '';

		if (count($categories) > 0) {
			foreach ($categories as $item) {
				$categoryIds .= $item->getId() . ",";
			}

			$categoryIds = rtrim($categoryIds, ",");
            return $categoryIds;
		}

        return '';
    }
}