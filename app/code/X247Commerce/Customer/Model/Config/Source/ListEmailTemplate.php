<?php

namespace X247Commerce\Customer\Model\Config\Source;

use Magento\Email\Model\Template\Config;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;

class ListEmailTemplate implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var CollectionFactory  */
    protected $_collectionFactory;

    private $emailTemplateConfig;

    public function __construct(Config $emailTemplateConfig, CollectionFactory $collectionFactory)
    {
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->_collectionFactory = $collectionFactory;
    }

    public function getEmailTemplateOptionArray()
    {
        return $this->emailTemplateConfig->getAvailableTemplates();
    }

    /**
    * Returns collection of custom templates
    *
    * @return mixed
    */
    public function getCustomTemplates()
    {
        return $this->_collectionFactory->create();
    }

    public function toOptionArray()
    {
        $listConfigEmailTemplate = $this->getEmailTemplateOptionArray();
        $listCustomEmailTempalte = $this->getCustomTemplates();
        $listOptions = [];

        foreach ($listConfigEmailTemplate as $item) {
            $label = gettype($item['label']) == 'string' ? $item['label'] : $item['label']->getText();
            $listOptions[] = ['value' => $item['value'], 'label' => $label];
        }

        foreach ($listCustomEmailTempalte as $item) {
            $listOptions[] = ['value' => $item->getTemplateId(), 'label' => $item->getTemplateCode()];
        }

        return $listOptions;
    }
}