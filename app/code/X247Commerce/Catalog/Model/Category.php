<?php
namespace X247Commerce\Catalog\Model;

class Category extends \Magento\Catalog\Model\Category
{
    /**
     * Retrieve array of product id's for category
     *
     * The array returned has the following format:
     * array($productId => $position)
     *
     * @return array
     */
    public function getProductsPopupPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('products_popup_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPopupPosition($this);
            $this->setData('products_popup_position', $array);
        }
        return $array;
    }
}