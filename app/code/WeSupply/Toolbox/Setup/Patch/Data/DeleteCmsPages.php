<?php
namespace WeSupply\Toolbox\Setup\Patch\Data;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Logger\Logger as Logger;

class DeleteCmsPages implements DataPatchInterface, PatchVersionInterface
{

    /**
     * WeSupply tracking page url
     */
    const WESUPPLY_TRACKING_ID = 'wesupply-tracking-info';

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
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param PageFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
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
        PageRepositoryInterface $pageRepository,
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
        $this->pageRepository = $pageRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        /**
         * delete 'wesupply-tracking-info' cms page as we do not use it anymore
         */
        $existingPage = $this->pageFactory->create()->load($this->getTrackingPageIdentifier());
        if ($existingPage->getId()) {
            try {
                $this->pageRepository->deleteById($existingPage->getId());
            } catch (NoSuchEntityException $e) {
                $message = __('WeSupply_Toolbox is trying to delete an existing cms page with URL key "%1" but an unknown error occurred! Please delete it manually if exists.', $this->getTrackingPageIdentifier());
                $this->logger->notice($message . ' ' . $e->getMessage());
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.4';
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
            AddCmsPages::class
        ];
    }


    /**
     * @return string
     */
    private function getTrackingPageIdentifier()
    {
        return self::WESUPPLY_TRACKING_ID;
    }
}
