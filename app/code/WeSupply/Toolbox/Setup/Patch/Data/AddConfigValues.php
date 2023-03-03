<?php
namespace WeSupply\Toolbox\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\StoreManagerInterface;

class AddConfigValues implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $state;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param State $state
     * @param StoreManagerInterface $storeManager
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        State $state,
        StoreManagerInterface $storeManager,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
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

        $connected = 0;
        $allStores = $this->getAllStores();

        foreach ($allStores as $storeId) {
            $isEnabled = $this->scopeConfig->getValue('wesupply_api/integration/wesupply_enabled', 'stores', $storeId);
            if ($isEnabled) {
                $connected++;
                $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 1, 'stores', $storeId);
            }

            if ($connected == count($allStores)) {
                $this->configWriter->save('wesupply_api/step_1/wesupply_connection_status', 1, 'default', 0);
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.6';
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
            AddEstimationAttribute::class
        ];
    }

    /**
     * @return array
     */
    private function getAllStores()
    {
        return array_values(array_map(function ($store) {
            return $store->getStoreId();
        }, $this->storeManager->getStores()));
    }
}
