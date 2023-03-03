<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block\Adminhtml;

class Index extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Ulmod_Productinquiry';
        $this->_controller = 'adminhtml_index';
        $this->_headerText = __('Productinquiry');
        $this->_addButtonLabel = __('Add New Product Inquiry');
        parent::_construct();
    }
}
