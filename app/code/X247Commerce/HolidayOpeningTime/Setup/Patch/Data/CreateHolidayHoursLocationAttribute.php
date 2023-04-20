<?php

namespace X247Commerce\HolidayOpeningTime\Setup\Patch\Data;

use Amasty\Storelocator\Model\AttributeFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateHolidayHoursLocationAttribute implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'holiday_hours';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AttributeFactory $attributeFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->attributeFactory->create()
            ->setFrontendLabel('Holiday Hours')
            ->setAttributeCode(self::ATTRIBUTE_CODE)
            ->setFrontendInput('text')
            ->save();
        $this->moduleDataSetup->endSetup();
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
}
