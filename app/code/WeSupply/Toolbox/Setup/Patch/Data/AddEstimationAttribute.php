<?php
namespace WeSupply\Toolbox\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class AddEstimationAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $state
     * @param CategorySetupFactory $categorySetupFactory
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $state,
        CategorySetupFactory $categorySetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->state = $state;
        $this->catalogSetupFactory = $categorySetupFactory;
        $areaCode = null;
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Exception $ex) {
        }
        if (!$areaCode) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $setup = $this->moduleDataSetup;

        $attributeName = 'wesupply_estimation_display';
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

        $catalogSetup->addAttribute(Product::ENTITY, $attributeName, [
            'type' => 'int',
            'label' => 'Display WeSupply Delivery Estimation',
            'input' => 'select',
            'required' => false,
            'sort_order' => 10,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'wysiwyg_enabled' => false,
            'is_html_allowed_on_front' => false,
            'group' => 'WeSupply Options',
            'default' => 1,
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'note' => 'WeSupply Delivery Estimation will not be displayed if the WeSupply Toolbox module is disabled.'
        ]);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.5';
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
            DeleteCmsPages::class
        ];
    }
}
