<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\Config\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Ulmod\Productinquiry\Model\Data
     */
    protected $data;

    /**
     * Constructor
     *
     * @param \Ulmod\Productinquiry\Model\Data $data
     */
    public function __construct(\Ulmod\Productinquiry\Model\Data $data)
    {
        $this->data = $data;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->data->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
