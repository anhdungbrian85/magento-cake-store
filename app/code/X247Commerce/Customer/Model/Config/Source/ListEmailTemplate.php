<?php

namespace X247Commerce\Customer\Model\Config\Source;

use Magento\Email\Model\Template\Config;

class ListEmailTemplate implements \Magento\Framework\Data\OptionSourceInterface
{
    private $emailTemplateConfig;

    public function __construct(Config $emailTemplateConfig)
    {
        $this->emailTemplateConfig = $emailTemplateConfig;
    }

    public function getEmailTemplateOptionArray()
    {
        return $this->emailTemplateConfig->getAvailableTemplates();
    }

    public function toOptionArray()
    {
        $listEmailTemplate = $this->getEmailTemplateOptionArray();
        $listOptions = [];

        foreach ($listEmailTemplate as $item) {
            $label = gettype($item['label']) == 'string' ? $item['label'] : $item['label']->getText();
            $listOptions[] = ['value' => $item['value'], 'label' => $label];
        }

        return $listOptions;
    }
}