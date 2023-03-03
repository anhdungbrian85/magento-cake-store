<?php
namespace X247Commerce\Nutritics\Block\Product\View;

class Nutritics extends \Magento\Framework\View\Element\Template
{
    protected $nutriticsApi;
    protected $_registry;
    public function __construct (
        \Magento\Framework\View\Element\Template\Context $context,
        \X247Commerce\Nutritics\Service\NutriticsApi $nutriticsApi,        
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->nutriticsApi = $nutriticsApi;
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
    public function getNutriticsInfo() 
    {
        $currentPoduct = $this->getCurrentProduct();
        $ifc = $currentPoduct->getIfcCode();
        return json_decode($this->nutriticsApi->getFoodProductByIfc($ifc), true);
    }
}