<?php
namespace X247Commerce\Nutritics\Block\Product\View;

class Nutritics extends \Magento\Framework\View\Element\Template
{
    protected $nutriticsApi;
    protected $nutriticsValueCollection;
    protected $_registry;
    public function __construct (
        \Magento\Framework\View\Element\Template\Context $context,
        \X247Commerce\Nutritics\Service\NutriticsApi $nutriticsApi,
        \X247Commerce\Nutritics\Model\ResourceModel\NutriticsValue\CollectionFactory $nutriticsValueCollection,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->nutriticsApi = $nutriticsApi;
        $this->nutriticsValueCollection = $nutriticsValueCollection;
        $this->_registry = $registry;
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCurrentProduct()
    {        
        return $this->_registry->registry('current_product');
    }

    /**
     * Fetch Nutritics data of current product from service
     * @param 
     * @return string
     */
    public function getNutriticsInfoFromApi() 
    {
        $currentPoduct = $this->getCurrentProduct();
        $ifc = $currentPoduct->getIfcCode();
        return json_decode($this->nutriticsApi->getNutriticsInfo($ifc), true);
    }

    /**
     * Get Nutritics data of current product in database
     * @param array $params 
     * @param $fields
     * @return string
     */
    public function getProductNutriticsInfoInDb() 
    {
        $currentPoduct = $this->getCurrentProduct();
        $nutriticsInfo = $this->nutriticsValueCollection->create()->addFieldToSelect('*')->addFieldToFilter('row_id', $currentPoduct->getRowId());
        // var_dump($nutriticsInfo->getData());die();
        return $nutriticsInfo->getData();
    }
    
}