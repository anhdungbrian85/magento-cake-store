<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block\Adminhtml\Index;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize inquiry edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'inquiry_id';
        $this->_blockGroup = 'Ulmod_Productinquiry';
        $this->_controller = 'adminhtml_index';

        parent::_construct();

        if ($this->_isAllowedAction('Ulmod_Productinquiry::save')) {
            $this->buttonList->update('save', 'label', __('Save Inquiry'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Ulmod_Productinquiry::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Inquiry'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('inquiry')->getId()) {
            return __(
                "Edit Product Inquiry '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('inquiry')->getName())
            );
        } else {
            return __('New Product Inquiry');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     *
     * Tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
