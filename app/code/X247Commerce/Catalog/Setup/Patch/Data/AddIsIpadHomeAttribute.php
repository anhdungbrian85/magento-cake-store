<?php
namespace X247Commerce\Catalog\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

class AddIsIpadHomeAttribute implements DataPatchInterface
{
     /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;
    /** @var EavSetupFactory */
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
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
			\Magento\Catalog\Model\Category::ENTITY,
			'is_active_ipad_home_page',
			[
                'type' => 'int',
                'label' => 'Show on Ipad Homepage',
                'input' => 'select',
                'source' => Boolean::class,
                'required' => true,
                'sort_order' => 30,
                'group' => 'General Information',
			]
		);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}