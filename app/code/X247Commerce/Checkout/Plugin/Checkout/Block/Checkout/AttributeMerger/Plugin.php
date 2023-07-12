<?php
namespace X247Commerce\Checkout\Plugin\Checkout\Block\Checkout\AttributeMerger;

class Plugin
{
  public function afterMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $result)
  {
    if (array_key_exists('street', $result)) {
      $result['street']['children'][0]['placeholder'] = __('Type first line of address and select from dropdown');
    }
    return $result;
  }
}