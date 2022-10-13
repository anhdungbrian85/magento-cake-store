<?php

namespace X247Commerce\DeliveryPopUp\Block\PopUp;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\FormKey;

class PopUp extends \Magento\Framework\View\Element\Template
{
 	protected $formKey;

 	public function __construct(
 		Context $context,
 		FormKey $formKey,
        array $data = []
 	) {
 		 $this->formKey = $formKey;
 		parent::__construct($context, $data);
 	}

 	 public function postCode()
    {
      return $this->getUrl('x247commerce_deliverypopup/index/index');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
} 