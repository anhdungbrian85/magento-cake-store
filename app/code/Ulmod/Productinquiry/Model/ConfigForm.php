<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Session\Generic as InquirySession;
        
class ConfigForm
{
    /**
     * @var CustomerSession
     */
    private $_customerSession;
    
    /**
     * @var CustomerViewHelper
     */
    private $_customerViewHelper;
    
    /**
     * @var array
     */
    private $_data;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param CustomerSession $customerSession
     * @param InquirySession $inquirySession
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CustomerSession $customerSession,
        InquirySession $inquirySession,
        CustomerViewHelper $customerViewHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_customerSession = $customerSession;
        $this->_data = $inquirySession->getFormData(true);
        $this->_customerViewHelper = $customerViewHelper;
    }
    
    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }
    
    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        if (!empty($this->_data['email'])) {
            return $this->_data['email'];
        }
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }

        $customer = $this->_customerSession->getCustomerDataObject();
        
        return $customer->getEmail();
    }
    
    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        if (!empty($this->_data['name'])) {
            return $this->_data['name'];
        }
        if (!$this->_customerSession->isLoggedIn()) {
            return '';
        }

        $customer = $this->_customerSession->getCustomerDataObject();
        return trim(
            $this->_customerViewHelper->getCustomerName($customer)
        );
    }
    
    /**
     * Get title
     *
     * @return string
     */
    public function getSubject()
    {
        $subject = '';
        if (!empty($this->_data['subject'])) {
            $subject = $this->_data['subject'];
        }
        return $subject;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone()
    {
        $telephone = '';
        if (!empty($this->_data['telephone'])) {
            $telephone = $this->_data['telephone'];
        }
        return $telephone;
    }
    
    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        $message = '';
        if (!empty($this->_data['message'])) {
            $message = $this->_data['message'];
        }
        return $message;
    }

    /**
     * Get extra field one
     *
     * @return string
     */
    public function getExtraFieldOne()
    {
        $extraFieldOne = '';
        if (!empty($this->_data['extra_field_one'])) {
            $extraFieldOne = $this->_data['extra_field_one'];
        }
        return $extraFieldOne;
    }

    /**
     * Get extra field two
     *
     * @return string
     */
    public function getExtraFieldTwo()
    {
        $extraFieldTwo = '';
        if (!empty($this->_data['extra_field_two'])) {
            $extraFieldTwo = $this->_data['extra_field_two'];
        }
        return $extraFieldTwo;
    }

    /**
     * Get extra field three
     *
     * @return string
     */
    public function getExtraFieldThree()
    {
        $extraFieldThree = '';
        if (!empty($this->_data['extra_field_three'])) {
            $extraFieldThree = $this->_data['extra_field_three'];
        }
        return $extraFieldThree;
    }

    /**
     * Get extra field four
     *
     * @return string
     */
    public function getExtraFieldFour()
    {
        $extraFieldFour = '';
        if (!empty($this->_data['extra_field_four'])) {
            $extraFieldFour = $this->_data['extra_field_four'];
        }
        return $extraFieldFour;
    }
}
