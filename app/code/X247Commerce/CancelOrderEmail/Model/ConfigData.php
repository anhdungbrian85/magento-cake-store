<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace X247Commerce\CancelOrderEmail\Model;

use Magento\Framework\Mail\Template\SenderResolverInterface;
use X247Commerce\CancelOrderEmail\Helper\Data;
class ConfigData
{
    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;
    protected $dataHelper;
    
    /**
     * @param SenderResolverInterface $senderResolver
     */
    public function __construct(
        SenderResolverInterface $senderResolver,
        Data $dataHelper
    ) {
        $this->senderResolver = $senderResolver;
        $this->dataHelper = $dataHelper;
    }
    public function getAutoReplySenderEmail()
    {
        $from = $this->dataHelper->getConfigValue('sales_email/order_cancel/identity');
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get auto reply sender name
     *
     * @return string
     */
    public function getAutoReplySenderName()
    {
        $from = $this->dataHelper->getConfigValue('sales_email/order_cancel/identity');
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }          
}
