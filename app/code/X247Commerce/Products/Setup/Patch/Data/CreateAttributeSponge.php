<?php

namespace X247Commerce\Products\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EavCollectionFactory;

class CreateAttributeSponge implements DataPatchInterface
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
        if( !$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'sponge')) {
            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'sponge');
        }

        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'sponge', [
           'type' => 'int',
           'label' => 'Sponge',
           'input' => 'select',
           'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
           'visible' => true,
           'required' => false,
           'user_defined' => true,
           'default' => '',
           'searchable' => true,
           'filterable' => true,
           'comparable' => true,
           'visible_on_front' => true,
           'used_in_product_listing' => false,
           'group' => 'General',
           'is_used_in_grid' => true,
           'visible_in_advanced_search' => true,
           'option' => [
                'values' => [
                    'Vanilla',
                    'Chocolate',
                    'Red Velvet',
                ]
           ]
       ]);

        $this->convertAttributeToSwatches();
    }

    public function convertAttributeToSwatches() {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'sponge');
        if (!$attribute) {
            return;
        }


        $attributeData['option'] = $this->addExistingOptions($attribute);
        $attributeData['frontend_input'] = 'select';
        $attributeData['swatch_input_type'] = 'text';
        $attributeData['update_product_preview_image'] = 1;
        $attributeData['use_product_image_for_swatch'] = 0;
        $attributeData['optiontext'] = $this->getOptionSwatch($attributeData);
        $attributeData['defaulttext'] = $this->getOptionDefaultText($attributeData);
        $attributeData['swatchtext'] = $this->getOptionSwatchText($attributeData);
        $attribute->addData($attributeData);
        $attribute->save();
    }

    protected function getOptionSwatch(array $attributeData)
    {
        $optionSwatch = ['order' => [], 'value' => [], 'delete' => []];
        $i = 0;
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['delete'][$optionKey] = '';
            $optionSwatch['order'][$optionKey] = (string)$i++;
            $optionSwatch['value']['option_' . $optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionSwatchText(array $attributeData)
    {
        $optionSwatch = ['value' => []];
        foreach ($attributeData['option'] as $optionKey => $optionValue) {
            $optionSwatch['value'][$optionKey] = [$optionValue, ''];
        }
        return $optionSwatch;
    }

    /**
     * @param array $attributeData
     * @return array
     */
    private function getOptionDefaultText(array $attributeData)
    {
        $optionSwatch = $this->getOptionSwatchText($attributeData);
        return [array_keys($optionSwatch['value'])[0]];
    }

    /**
     * @param $attributeId
     * @return void
     */
    private function loadOptionCollection($attributeId)
    {
        if (empty($this->optionCollection[$attributeId])) {
            $this->optionCollection[$attributeId] = $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($attributeId)
                ->setPositionOrder('asc', true)
                ->load();
        }
    }

    private function addExistingOptions(EavAttribute $attribute)
    {
        $options = [];
        $attributeId = $attribute->getId();
        if ($attributeId) {
            $this->loadOptionCollection($attributeId);
            /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */

            foreach ($this->optionCollection[$attributeId] as $option) {
                if ( ! empty( $option->getValue() ) ) {
                    $options[$option->getId()] = $option->getValue();
                }
                
            }
        }

        return $options;
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
