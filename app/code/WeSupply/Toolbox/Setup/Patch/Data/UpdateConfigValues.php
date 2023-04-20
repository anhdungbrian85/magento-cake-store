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

class UpdateConfigValues implements DataPatchInterface, PatchVersionInterface
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
         * since 1.0.8 step_1 and step_2 groups were removed
         * so we have to copy the old saved value into the new config path
         */
        $allStores = $this->getAllStores();
        array_push($allStores, 0); // id for default scope

        $preserveSettings = [
            'step_1' => [
                'wesupply_client_id',
                'wesupply_client_secret',
                'wesupply_connection_status'
            ],
            'step_2' => [
                'wesupply_subdomain',
                'access_key'
            ]
        ];

        $copiedClientName = false;
        foreach ($allStores as $storeId) {
            foreach ($preserveSettings as $group => $fields) {
                foreach ($fields as $field) {
                    $scopeType = $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                    if ($existing = $this->scopeConfig->getValue('wesupply_api/' . $group . '/' . $field, $scopeType, $storeId)) {
                        $this->configWriter->save('wesupply_api/integration/' . $field, $existing, $scopeType, $storeId);
                        if ($field == 'wesupply_subdomain') {
                            $copiedClientName = true;
                        }
                    }
                }
            }
        }

        /**
         * Auto generate and save a default Access Key if this is a first install
         */
        if (!$copiedClientName) {
            $this->configWriter->save('wesupply_api/integration/' . 'access_key', $this->random_str(40), 'default', '0');
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.8';
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
            AddConfigValues::class
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
