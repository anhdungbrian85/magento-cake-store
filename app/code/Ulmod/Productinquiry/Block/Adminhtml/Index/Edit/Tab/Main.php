<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Block\Adminhtml\Index\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Main extends Generic implements TabInterface
{
    /**
     * @var SystemStore
     */
    protected $_systemStore;

    /**
     * @var ConfigData
     */
    protected $configData;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param SystemStore $systemStore
     * @param ConfigData $configData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SystemStore $systemStore,
        ConfigData $configData,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->configData = $configData;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Ulmod\Inquiry\Model\Data $model */
        $model = $this->_coreRegistry->registry('inquiry');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Ulmod_Productinquiry::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('inquiry_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Info')]);
        $fieldset->addType('image', \Ulmod\Productinquiry\Block\Adminhtml\Index\Helper\Image::class);

        if ($model->getInquiryId()) {
            $fieldset->addField('inquiry_id', 'hidden', ['name' => 'inquiry_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        $fieldset->addField(
            'telephone',
            'text',
            [
                'name' => 'telephone',
                'label' => __('Telephone'),
                'title' => __('Telephone'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Attachment'),
                'title' => __('Attachment'),
                'required' => false,
                'note' => __('Note: when choosing the file, make sure that 
					the name is not longer than 90 characters'),
                'disabled' => $isElementDisabled
            ]
        );
        
         $fieldset->addField(
             'product_name',
             'text',
             [
                'name' => 'product_name',
                'label' => __('Product name'),
                'title' => __('Product name'),
                'required' => false,
                'disabled' => $isElementDisabled
             ]
         );

        $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('Product sku'),
                'title' => __('Product sku'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'subject',
            'text',
            [
                'name' => 'subject',
                'label' => __('Subject'),
                'title' => __('Subject'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'message',
            'textarea',
            [
                'name' => 'message',
                'label' => __('Message'),
                'title' => __('Message'),
                'disabled' => $isElementDisabled,
                'required' => true
            ]
        );

        $fieldset = $form->addFieldset('additional_fieldset', ['legend' => __('Additional Info')]);

        $fieldset->addField(
            'current_page_url',
            'text',
            [
                'name' => 'current_page_url',
                'label' => __('Page Source Url'),
                'title' => __('Page Source Url'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        if ($this->configData->isExtraFieldOneEnabled() == 1) {
            if ($this->configData->getExtraFieldOneType() == 'text') {
                $fieldset->addField(
                    'extra_field_one',
                    'text',
                    [
                        'name' => 'extra_field_one',
                        'label' => $this->configData->getExtraFieldOneLabel(),
                        'title' => $this->configData->getExtraFieldOneLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldOneType() == 'checkbox') {
                $fieldset->addField(
                    'extra_field_one',
                    'text',
                    [
                      'name' => 'extra_field_one',
                      'label' => $this->configData->getExtraFieldOneLabel(),
                      'title' => $this->configData->getExtraFieldOneLabel(),
                      'disabled' => $isElementDisabled,
                      'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldOneType() == 'textarea') {
                $fieldset->addField(
                    'extra_field_one',
                    'textarea',
                    [
                      'name' => 'extra_field_one',
                      'label' => $this->configData->getExtraFieldOneLabel(),
                      'title' => $this->configData->getExtraFieldOneLabel(),
                      'disabled' => $isElementDisabled,
                      'required' => false
                    ]
                );
            }
        }

        if ($this->configData->isExtraFieldTwoEnabled() == 1) {
            if ($this->configData->getExtraFieldTwoType() == 'text') {
                $fieldset->addField(
                    'extra_field_two',
                    'text',
                    [
                        'name' => 'extra_field_two',
                        'label' => $this->configData->getExtraFieldTwoLabel(),
                        'title' => $this->configData->getExtraFieldTwoLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldTwoType() == 'checkbox') {
                $fieldset->addField(
                    'extra_field_two',
                    'text',
                    [
                      'name' => 'extra_field_two',
                      'label' => $this->configData->getExtraFieldTwoLabel(),
                      'title' => $this->configData->getExtraFieldTwoLabel(),
                      'disabled' => $isElementDisabled,
                      'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldTwoType() == 'textarea') {
                $fieldset->addField(
                    'extra_field_two',
                    'textarea',
                    [
                        'name' => 'extra_field_two',
                        'label' => $this->configData->getExtraFieldTwoLabel(),
                        'title' => $this->configData->getExtraFieldTwoLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            }
        }
        
        if ($this->configData->isExtraFieldThreeEnabled() == 1) {
            if ($this->configData->getExtraFieldThreeType() == 'text') {
                $fieldset->addField(
                    'extra_field_three',
                    'text',
                    [
                        'name' => 'extra_field_three',
                        'label' => $this->configData->getExtraFieldThreeLabel(),
                        'title' => $this->configData->getExtraFieldThreeLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldThreeType() == 'checkbox') {
                $fieldset->addField(
                    'extra_field_three',
                    'text',
                    [
                      'name' => 'extra_field_three',
                      'label' => $this->configData->getExtraFieldThreeLabel(),
                      'title' => $this->configData->getExtraFieldThreeLabel(),
                      'disabled' => $isElementDisabled,
                      'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldThreeType() == 'textarea') {
                $fieldset->addField(
                    'extra_field_three',
                    'textarea',
                    [
                        'name' => 'extra_field_three',
                        'label' => $this->configData->getExtraFieldThreeLabel(),
                        'title' => $this->configData->getExtraFieldThreeLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            }
        }

        if ($this->configData->isExtraFieldFourEnabled() == 1) {
            if ($this->configData->getExtraFieldFourType() == 'text') {
                $fieldset->addField(
                    'extra_field_four',
                    'text',
                    [
                        'name' => 'extra_field_four',
                        'label' => $this->configData->getExtraFieldFourLabel(),
                        'title' => $this->configData->getExtraFieldFourLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldFourType() == 'checkbox') {
                $fieldset->addField(
                    'extra_field_four',
                    'text',
                    [
                      'name' => 'extra_field_four',
                      'label' => $this->configData->getExtraFieldFourLabel(),
                      'title' => $this->configData->getExtraFieldFourLabel(),
                      'disabled' => $isElementDisabled,
                      'required' => false
                    ]
                );
            } elseif ($this->configData->getExtraFieldFourType() == 'textarea') {
                $fieldset->addField(
                    'extra_field_four',
                    'textarea',
                    [
                        'name' => 'extra_field_four',
                        'label' => $this->configData->getExtraFieldFourLabel(),
                        'title' => $this->configData->getExtraFieldFourLabel(),
                        'disabled' => $isElementDisabled,
                        'required' => false
                    ]
                );
            }
        }

        $fieldset = $form->addFieldset('storeview_fieldset', ['legend' => __('Store View & Status')]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'values' => $this->_systemStore
                        ->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled,
                    'required' => true
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name' => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
            $model->setStoreId(
                $this->_storeManager->getStore(true)->getId()
            );
        }

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $fieldset->addField(
            'date',
            'date',
            [
                'label' => __('Created date'),
                'title' => __('Created date'),
                'name' => 'date',
                'date_format' => $dateFormat,
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param int $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
