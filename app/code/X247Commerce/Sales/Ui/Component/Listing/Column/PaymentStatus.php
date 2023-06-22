<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Sales\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Address
 */
class PaymentStatus extends Column
{
    /**
     * @var Escaper
     */
    

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $columnName = $this->getData('name');
            foreach ($dataSource['data']['items'] as $key => $item) {
                $dataSource['data']['items'][$key][$columnName] = $this->isPayment($item);
            }
        }
        return $dataSource;
    }

    protected function isPayment($item){
        return $item['base_amount_paid'] ? __('Paid') : __('Not Paid'); 
    }
}
