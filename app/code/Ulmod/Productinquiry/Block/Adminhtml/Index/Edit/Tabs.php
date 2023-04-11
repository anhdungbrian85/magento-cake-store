<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block\Adminhtml\Index\Edit;

use Magento\Backend\Block\Template\Context;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('inquiry_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product Inquiry Info'));
    }
}
