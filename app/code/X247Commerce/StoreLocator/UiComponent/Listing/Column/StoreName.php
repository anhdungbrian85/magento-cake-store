<?php

namespace X247Commerce\StoreLocator\UiComponent\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Amasty\Storelocator\Model\LocationFactory;

class StoreName extends Column
{
    protected $locationFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param LocationFactory $locationFactory
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LocationFactory $locationFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->locationFactory = $locationFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');//store_id
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[$fieldName] != '') {
                    $storeName = $this->getStoreName($item[$fieldName]);
                    $item[$fieldName] = $storeName;
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $locationId
     * @return string
     */
    private function getStoreName($locationId)
    {
        $location = $this->locationFactory->create()->load($locationId);
        $name = $location->getName();
        return $name;
    }
}