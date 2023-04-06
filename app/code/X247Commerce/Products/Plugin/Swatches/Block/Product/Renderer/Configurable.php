<?php
namespace X247Commerce\Products\Plugin\Swatches\Block\Product\Renderer;
 
class Configurable
{
    public function afterGetJsonConfig(\Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result) {
 
        $jsonResult = json_decode($result, true);
        $jsonResult['skus'] = [];
        $jsonResult['lead_delivery'] = [];
 
        foreach ($subject->getAllowProducts() as $simpleProduct) {
           $jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
           $jsonResult['lead_delivery'][$simpleProduct->getId()] = $simpleProduct->getLeadDelivery();
        }
        $result = json_encode($jsonResult);
        return $result;
    }
}