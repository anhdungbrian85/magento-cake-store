<?php

declare(strict_types=1);

namespace X247Commerce\Checkout\Setup\Patch\Data;

use Amasty\Storelocator\Model\AttributeFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class CreateIntervalTimeSlot implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'interval_time_slot';

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
            ->setFrontendLabel('Interval Time Slot')
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
