<?php 
namespace X247Commerce\Theme\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class CategoryHelper extends \Magento\Catalog\Helper\Category
{
    protected $categoryRepository;
    
    const XML_PATH_CATEGORY_ID_IPAD_HOME_VIEW = 'x247commerce_theme/ipad_home_page/category_ipad_home_page';
    
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $categoryFactory, $storeManager, $dataCollectionFactory, $categoryRepository);
    }

    public function getCateIDIpadHomeView()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CATEGORY_ID_IPAD_HOME_VIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}

?>