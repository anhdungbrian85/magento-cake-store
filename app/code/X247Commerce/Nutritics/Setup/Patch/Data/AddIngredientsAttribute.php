<?php

namespace X247Commerce\Nutritics\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddIngredientsAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->addNutriticsAttributeData();
    }

    /**
     * Add nutritics_attribute Data
     * @return void
     *
     */
    private function addNutriticsAttributeData()
    {
        $nutriticsAttributeTable = $this->moduleDataSetup->getTable('nutritics_attribute');
        $connection = $this->moduleDataSetup->getConnection();
        $attribute = ['value' => 'quid', 'label' => 'quid', 'unit' => '', 'group' => 'Miscellaneous'];
               
        // try {
            $connection->insert($nutriticsAttributeTable, [
                'attribute_code' => $attribute['value'],
                'attribute_name' => $attribute['label'],
                'attribute_unit' => isset($attribute['unit']) ? $attribute['unit'] : '',
                'group_code' => isset($attribute['group']) ? $attribute['group'] : '',
            ]);

        // } catch (\Exception $e) {
        //     //@todo: Log something
        // }
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
