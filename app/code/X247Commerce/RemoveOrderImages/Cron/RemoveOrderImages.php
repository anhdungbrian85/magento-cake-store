<?php

namespace X247Commerce\RemoveOrderImages\Cron;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Session\SessionManagerInterface;

class RemoveOrderImages
{
    protected $logger;
    protected $orderCollectionFactory;
    protected $filesystem;
    protected $file;
    protected $resourceConnection;
    protected $coreSession;

    public function __construct(
        LoggerInterface $logger,
        OrderCollectionFactory $orderCollectionFactory,
        Filesystem $filesystem,
        File $file,
        ResourceConnection $resourceConnection,
        SessionManagerInterface $coreSession
    )
    {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->resourceConnection = $resourceConnection;
        $this->coreSession = $coreSession;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $statuses = array( 'complete', 'canceled' );

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('status', ['in' => $statuses] )
            ->addFieldToFilter('remove_images_flag', ['neq' => 1])
            ;
        $limit = $this->coreSession->getLimitCompleteOrder();
        if (empty($limit)) {
            $limit = 50;
        }
        var_dump($limit);
        $this->coreSession->unsLimitCompleteOrder();
        $collection->getSelect()->limit($limit);
        var_dump(count($collection));
        foreach ($collection as $order) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/remove_order_images.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            try {
                foreach ($order->getAllVisibleItems() as $orderItem) {
                        $options = $orderItem->getProductOptions();
                        
                        $optionDetails = [];
                        if(!empty($options["options"])) {
                            foreach($options["options"] as $option) {
                                if(!empty($option["option_value"])) {
                                    $optionDetails = $option["option_value"];
                                }
                            }
                        }
                        
                        if ($optionDetails) {
                            $detail = json_decode($optionDetails, true);
                            $quotePath = !empty($detail["quote_path"]) ? $detail["quote_path"] : '';
                            $orderPath = !empty($detail["order_path"]) ? $detail["order_path"] : '';

                            $mediaRootDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                            
                            if ($quotePath && $this->file->isExists($mediaRootDir . $quotePath)) {
                                $this->file->deleteFile($mediaRootDir . $quotePath);
                            }
                            if ($orderPath && $this->file->isExists($mediaRootDir . $orderPath)) {
                                $this->file->deleteFile($mediaRootDir . $orderPath);
                            }
                            if ($orderPath || $quotePath) {
                                $logger->info('Remove images of order with entity_id: '.$order->getId(). ', increment_id: '.$order->getIncrementId());
                            }
                        }
                }
            } catch (Exception $e) {
                $logger->info('Cannot remove images of order with entity_id: '.$order->getId(). ', increment_id: '.$order->getIncrementId(). ' - '.$e->getMessage());
            }
            $this->saveRemoveImagesFlag($order);
        }
    }
    /**
     * @return void
     */
    private function saveRemoveImagesFlag($order): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $data = ["remove_images_flag" => 1];
        $where = ['entity_id = ?' => $order->getId()];
        $connection->update($tableName, $data, $where);
    }
}

