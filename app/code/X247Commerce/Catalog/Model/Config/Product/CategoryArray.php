<?php
namespace X247Commerce\Catalog\Model\Config\Product;
 
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
 
class CategoryArray extends AbstractSource
{
    protected $optionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->_storeManager = $storeManager;
    }

    public function getAllOptions()
    {
    	$this->_options = [];
    	$categories = $this->categoryCollection->create()->addAttributeToSelect('*')->addIsActiveFilter()->setStore($this->_storeManager->getStore());

    	foreach ($categories as $category) {
    		if ($category->getLevel() > 1) {
    			$this->_options[] = ['label' => $category->getName(), 'value' => $category->getId()];
    		}
    	}
    
        return $this->_options;
    }
}