<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Yext\Model\Config\Source;

use Exception;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class Stocks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockRepositoryInterface $stockRepository,
        LoggerInterface $logger
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockRepository = $stockRepository;
        $this->logger = $logger;
    }

    /**
     * @return StockInterface[]|null
     */
    public function getStocksList()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $stockInfo = null;
        try {
            $stockData = $this->stockRepository->getList($searchCriteria);
            if ($stockData->getTotalCount()) {
                $stockInfo = $stockData->getItems();
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $stockInfo;
    }

    public function toOptionArray()
    {
        $stockList = $this->getStocksList();
        $stockOption = [];
        foreach ($stockList as $stock) {
            $stockOption[] = ['value' => $stock->getId(), 'label' => $stock->getName()];
        }
        return $stockOption;
    }
}

