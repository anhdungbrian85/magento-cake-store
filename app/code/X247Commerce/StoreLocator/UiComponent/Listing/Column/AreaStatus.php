<?php

namespace X247Commerce\StoreLocator\UiComponent\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class AreaStatus extends Column
{
    protected $locationFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
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

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');//status
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[$fieldName] != '') {
                    $item[$fieldName] = ($item[$fieldName] == 0) ? 'BlackListed' : 'WhiteListed';
                }
            }
        }
        return $dataSource;
    }
}