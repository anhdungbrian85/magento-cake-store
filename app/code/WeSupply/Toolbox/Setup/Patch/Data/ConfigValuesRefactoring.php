<?php
namespace WeSupply\Toolbox\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigValuesRefactoring implements DataPatchInterface, PatchVersionInterface
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

        /**
         * since 1.0.14 wesupply_order_export and step_3 groups were moved under advanced_settings
         * also, step_3 was renamed into wesupply_view_settings
         * so we have to copy the old saved value into the new config path
         */
        $stores = ['0'];  // id for default scope
        $allStores = $this->getAllStores();
        $stores = array_merge($stores, $allStores);

        $preserveSettings = [
            'wesupply_order_export' => [
                'wesupply_order_filter',
                'wesupply_order_filter_countries',
                'wesupply_order_filter_pending',
                'wesupply_order_filter_complete',
                'wesupply_order_product_attributes',
                'item_weight_attr',
                'item_width_attr',
                'item_height_attr',
                'item_length_attr',
                'wesupply_order_product_attributes_fetch'
            ],
            'step_3' => [
                'wesupply_order_view_enabled',
                'enable_delivery_estimations_header_link',
                'wesupply_order_view_iframe',
                'wesupply_tracking_info_iframe',
                'wesupply_admin_order_enabled',
                'wesupply_admin_returns_enabled'
            ]
        ];

        $configRenamed = 'step_3';
        $configReplacement = 'wesupply_view_settings';

        foreach ($stores as $storeId) {
            foreach ($preserveSettings as $group => $fields) {
                foreach ($fields as $field) {
                    $scopeType = $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                    if ($existing = $this->scopeConfig->getValue('wesupply_api/' . $group . '/' . $field, $scopeType, $storeId)) {
                        if (
                            $storeId !== '0' &&
                            $existing === $this->scopeConfig->getValue('wesupply_api/' . $group . '/' . $field, $scopeType, '0')
                        ) {
                            continue;
                        }

                        $this->configWriter->save(
                            'wesupply_api/advanced_settings/' .
                            str_replace($configRenamed, $configReplacement, $group) . '/' . $field,
                            $existing,
                            $scopeType,
                            $storeId
                        );
                    }
                }
            }
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.14';
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
            UpdateConfigValues::class
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

    /**
     * @param $length
     * @param string $keyspace
     * @return string
     * @throws \Exception
     */
    private function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
