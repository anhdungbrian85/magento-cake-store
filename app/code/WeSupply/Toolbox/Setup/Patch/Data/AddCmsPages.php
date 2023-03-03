<?php
namespace WeSupply\Toolbox\Setup\Patch\Data;

use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Logger\Logger as Logger;
use Magento\Store\Model\ScopeInterface;


class AddCmsPages implements DataPatchInterface, PatchVersionInterface
{

    /**
     * WeSupply tracking page url
     */
    const WESUPPLY_TRACKING_ID = 'wesupply-tracking-info';

    /**
     * WeSupply store locator page url
     */
    const WESUPPLY_STORE_LOCATOR_ID = 'wesupply-store-locator';

    /**
     * WeSupply store-details page url
     */
    const WESUPPLY_STORE_DETAILS_ID = 'wesupply-store-details';


    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param PageFactory $pageFactory
     * @param StoreManagerInterface $storeManager
     * @param Logger $logger
     * @param State $state
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        PageFactory $pageFactory,
        StoreManagerInterface $storeManager,
        Logger $logger,
        State $state
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
        $areaCode = null;
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Exception $ex) {
        }
        if (!$areaCode) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }

        $this->pageFactory = $pageFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $cmsPages = [
            [
                'title' => 'Tracking Info',
                'identifier' => $this->getTrackingPageIdentifier()
            ],
            [
                'title' => 'Store Locator',
                'identifier' => $this->getStoreLocatorPageIdentifier()
            ],
            [
                'title' => 'Store Details',
                'identifier' => $this->getStoreDetailsPageIdentifier()
            ]
        ];

        foreach ($cmsPages as $pageData) {
            $page = $this->pageFactory->create()
                ->setTitle($pageData['title'])
                ->setIdentifier($pageData['identifier'])
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($this->createIframeContainer());

            try {
                $page->save();
            } catch (\Exception $e) {
                $message = __('WeSupply_Toolbox is trying to create a cms page with URL key "%1" but this identifier already exists!', $pageData['identifier']);
                $this->logger->notice($message . ' ' . $e->getMessage());
            }
        }

        /**
         * since 1.0.3 wesupply_subdomaine was moved from step_1 to step_2
         * so we have to copy the old saved value into the new config path if exists
         */
        if ($wesupplySubdomain = $this->scopeConfig->getValue('wesupply_api/step_1/wesupply_subdomain', ScopeInterface::SCOPE_STORE)) {
            $this->configWriter->save('wesupply_api/step_2/wesupply_subdomain', $wesupplySubdomain, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.3';
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
        return [];
    }


    /**
     * @return string
     */
    private function getTrackingPageIdentifier()
    {
        return self::WESUPPLY_TRACKING_ID;
    }

    /**
     * @return string
     */
    private function getStoreLocatorPageIdentifier()
    {
        return self::WESUPPLY_STORE_LOCATOR_ID;
    }

    /**
     * @return string
     */
    private function getStoreDetailsPageIdentifier()
    {
        return self::WESUPPLY_STORE_DETAILS_ID;
    }

    /**
     * @return string
     */
    private function createIframeContainer()
    {
        $container  = '<!-- Do not delete or edit this container -->' . "\n";
        $container .= '<div class="embedded-iframe-container"></div>';

        return $container;
    }
}
