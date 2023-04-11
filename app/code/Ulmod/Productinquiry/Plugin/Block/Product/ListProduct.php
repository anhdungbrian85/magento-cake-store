<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Plugin\Block\Product;

use Ulmod\Productinquiry\Model\ConfigData;

class ListProduct
{
    /**
     * @var ConfigData
     */
    protected $configData;
    
    /**
     * @param ConfigData $configData
     */
    public function __construct(
        ConfigData $configData
    ) {
        $this->configData = $configData;
    }
    
    /**
     * Add product inquiry link
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function aroundGetProductDetailsHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $html = $subject->getLayout()
          ->createBlock(\Ulmod\Productinquiry\Block\ProductinquiryList::class)
          ->setProduct($product)
          ->setTemplate('Ulmod_Productinquiry::productinquiry_list_link.phtml')
          ->toHtml();
          
        $result = $proceed($product);

        $enabled = $this->configData->isExtensionEnabled();
        if ($enabled) {
            if ($this->configData->isCategoryPage() || $this->configData->isCatalogSearchPage()) {
                if (!$this->configData->isEnableInquiryOnCategory()) {
                    return  $result . $html;
                } else {
                    return $result;
                }
            } else {
                return  $result . $html;
            }
        }
        
        return $result;
    }
}
