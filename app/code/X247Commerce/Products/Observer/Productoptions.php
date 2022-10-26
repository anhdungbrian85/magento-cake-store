<?php
namespace X247Commerce\Products\Observer;

use Magento\Framework\Event\ObserverInterface;
 
class Productoptions implements ObserverInterface
{
	protected $_options;
 
	public function __construct(
        \Magento\Catalog\Model\Product\OptionFactory $options
	) {
        $this->_options = $options;
	}
 
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $product = $observer->getProduct();
   	    $id = $product->getId();
        $options = [
                'sort_order' => '1',
                'title' => 'Number',
         	   'price_type' => 'fixed',
                'price' => '0',
                'type' => 'field',
                'is_require' => '1',
                'max_characters' => '3',
                'sku' =>'input-number'
    	];

        $collection = $this->_options->create()->getCollection()->addFieldToFilter('product_id', ['eq' => $id]);
        if (count($collection)==0) {
            $option = $this->_options->create()
                ->setProductId($product->getId())
                ->setStoreId($product->getStoreId())
                ->addData($options);
            $option->save();
            $product->addOption($option);
        }
	}
}