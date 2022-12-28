<?php

namespace X247Commerce\Nutritics\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvReader;

class AddAttributeData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
    * @var CsvReader
    */
    private $csvReader;

    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param DirectoryList $directoryList
     * @param CsvReader $csvReader
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        DirectoryList $directoryList,
        CsvReader $csvReader
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->directoryList = $directoryList;
        $this->csvReader = $csvReader;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->addGroupAttributeData();
        $this->addNutriticsAttributeData();
    }

    /**
     * Add group_attribute Data
     * @return void
     *
     */
    private function addGroupAttributeData()
    {
        $groupAttributeTable = $this->moduleDataSetup->getTable('nutritics_attribute_group');
        $connection = $this->moduleDataSetup->getConnection();
        $groupData = $this->getGroupAttributeData();
        // var_dump($groupData);die();
        if ($groupAttributeTable) {
            foreach ($groupData as $group) {
               
                // try {
                    $connection->insert($groupAttributeTable, [
                        'group_code' => $group['value'],
                        'group_name' => $group['label']
                    ]);

                // } catch (\Exception $e) {
                //     //@todo: Log something
                // }
            }
        }

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
        $attributesData = $this->getNutriticsAttributeData();
        
        if ($nutriticsAttributeTable) {
            foreach ($attributesData as $attribute) {
               
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
        }

    }

    private function getGroupAttributeData()
    {
        $getGroupAttributeData = [
            ['value' => 'Energy', 'label' => 'Energy'],
            ['value' => 'Macro', 'label' => 'Macronutrients'],
            ['value' => 'Carbohydrates', 'label' => 'Carbohydrates'],
            ['value' => 'Fats', 'label' => 'Lipid Components'],
            ['value' => 'Minerals', 'label' => 'Minerals and Trace Elements'],
            ['value' => 'Vitamins', 'label' => 'Vitamins'],
            ['value' => 'Other', 'label' => 'Other'],
            ['value' => 'Miscellaneous', 'label' => 'Miscellaneous'],
        ];

        return $getGroupAttributeData;
    }
    private function getNutriticsAttributeData()
    {
        $attributeData = [['value' => 'energyKcal', 'label' => 'Energy Kcal', 'unit' => 'kcal', 'group' => 'Energy'],['value' => 'energyKj', 'label' => 'Energy Kj', 'unit' => 'kJ', 'group' => 'Energy'],['value' => 'carbohydrate', 'label' => 'Carbohydrate', 'unit' => 'g', 'group' => 'Macro'],['value' => 'protein', 'label' => 'Protein', 'unit' => 'g', 'group' => 'Macro'],['value' => 'fat', 'label' => 'Fat', 'unit' => 'g', 'group' => 'Macro'],['value' => 'water', 'label' => 'Water (Moisture)', 'unit' => 'g', 'group' => 'Macro'],['value' => 'waterDr', 'label' => 'Water from Drinks', 'unit' => 'g', 'group' => 'Macro'],['value' => 'alcohol', 'label' => 'Alcohol', 'unit' => 'g', 'group' => 'Macro'],['value' => 'starch', 'label' => 'Starch', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'oligosaccharide', 'label' => 'Oligosaccharide', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'fibre', 'label' => 'Fibre', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'nsp', 'label' => 'NSP (Englyst)', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'sugars', 'label' => 'Sugars', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'freesugars', 'label' => 'Free Sugars', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'Glucose', 'label' => 'glucose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'galactose', 'label' => 'Galactose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'fructose', 'label' => 'Fructose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'sucrose', 'label' => 'Sucrose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'maltose', 'label' => 'maltose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'lactose', 'label' => 'lactose', 'unit' => 'g', 'group' => 'Carbohydrates'],['value' => 'satfat', 'label' => 'Saturated Fat', 'unit' => 'g', 'group' => 'Fats'],['value' => 'monos', 'label' => 'Monounsaturated Fat', 'unit' => 'g', 'group' => 'Fats'],['value' => 'cismonos', 'label' => 'cismonos', 'unit' => 'g', 'group' => 'Fats'],['value' => 'poly', 'label' => 'Polyunsaturated Fat', 'unit' => 'g', 'group' => 'Fats'],['value' => 'n3poly', 'label' => 'Omega-3 (Total)', 'unit' => 'g', 'group' => 'Fats'],['value' => 'n6poly', 'label' => 'Omega-6 (Total)', 'unit' => 'g', 'group' => 'Fats'],['value' => 'cispoly', 'label' => 'cispoly', 'unit' => 'g', 'group' => 'Fats'],['value' => 'trans', 'label' => 'Trans Fats (Total)', 'unit' => 'g', 'group' => 'Fats'],['value' => 'cholesterol', 'label' => 'Cholesterol', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'sodium', 'label' => 'Sodium (Na)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'potassium', 'label' => 'Potassium (K)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'chloride', 'label' => 'Chloride (Cl)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'calcium', 'label' => 'Calcium (Ca)', 'unit' => 'g', 'group' => 'Minerals'],['value' => 'phosphorus', 'label' => 'Phosphorus (P)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'magnesium', 'label' => 'Magnesium (Mg)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'iron', 'label' => 'Iron (Fe)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'zinc', 'label' => 'Zinc (Zn)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'copper', 'label' => 'Copper (Cu)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'manganese', 'label' => 'Manganese (Mn)', 'unit' => 'mg', 'group' => 'Minerals'],['value' => 'selenium', 'label' => 'Selenium (Se)', 'unit' => 'ug', 'group' => 'Minerals'],['value' => 'iodine', 'label' => 'Iodine (I)', 'unit' => 'ug', 'group' => 'Minerals'],['value' => 'vita', 'label' => 'Vitamin A (Total RE)', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'retinol', 'label' => 'Retinol (preformed)', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'carotene', 'label' => 'Carotene', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'vitd', 'label' => 'Vitamin D', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'vite', 'label' => 'Vitamin E', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'vitk', 'label' => 'Vitamin K1 (Phylloquinone)', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'thiamin', 'label' => 'Thiamin (B1)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'riboflavin', 'label' => 'Riboflavin (B2)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'niacineqv', 'label' => 'Niacin (B3) (Total NE)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'niacin', 'label' => 'Niacin (preformed)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'tryptophan', 'label' => 'Tryptophan', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'pantothenate', 'label' => 'Pantothenate (B5)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'vitb6', 'label' => 'Vitamin B6 (Pyridoxine)', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'folate', 'label' => 'Folates (B9) Total', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'vitb12', 'label' => 'Vitamin B12 (Cobalamin)', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'biotin', 'label' => 'Biotin (B7)', 'unit' => 'ug', 'group' => 'Vitamins'],['value' => 'vitc', 'label' => 'Vitamin C', 'unit' => 'mg', 'group' => 'Vitamins'],['value' => 'gi', 'label' => 'GI', 'unit' => '', 'group' => 'Other'],['value' => 'gl', 'label' => 'GL', 'unit' => '', 'group' => 'Other'],['value' => 'caffeine', 'label' => 'Caffeine', 'unit' => 'mg', 'group' => 'Other'],['value' => 'allergens', 'label' => 'Allergens', 'unit' => '', 'group' => 'Miscellaneous']];

        return $attributeData;
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
