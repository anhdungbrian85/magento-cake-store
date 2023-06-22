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
    protected $mollieApi;
    protected $storeId;
    protected $storeManager;
    protected $logger;
    protected $paymentCollection;
    protected $paymentFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Mollie\Payment\Model\Api $mollieApi,
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $paymentCollection,
        \Magento\Sales\Model\Order\PaymentFactory $paymentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->mollieApi = $mollieApi;
        $this->storeManager = $storeManager;        
        $this->logger = $logger;        
        $this->paymentCollection = $paymentCollection;        
        $this->paymentFactory = $paymentFactory;        

    }


    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $columnName = $this->getData('name');
            foreach ($dataSource['data']['items'] as $key => $item) {
                $payment = $this->paymentCollection->addFieldToFilter('parent_id', $item['entity_id'])->getFirstItem()->getData('additional_information');
                    $dataSource['data']['items'][$key][$columnName] = isset( $payment['payment_status']) ?  $payment['payment_status'] : '';
            }
        }
        return $dataSource;
    }
}