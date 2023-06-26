<?php
namespace X247Commerce\Catalog\Plugin;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class InventorySaveDebugger
{
    public function beforeBeforeSave(StockItemInterface $subject)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$subject->getIsInStock()) {
            try {
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/inventory.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('Start debugging!'); // Print string type data

                // Log current Url
                $urlInterface = $om->get(\Magento\Framework\UrlInterface::class);
                $currentUrl = $urlInterface->getCurrentUrl();
                $logger->info('Current Url: '.$currentUrl);

                // Log Request
                $request = $om->get(\Magento\Framework\App\RequestInterface::class);
                $logger->info('Action name: '. $request->getFullActionName());

                $logger->info('Params: '. print_r($request->getParams(), true));

                // Log Backend data
                $authSession = $om->get(\Magento\Backend\Model\Auth\Session::class);
                $logger->info('Admin: '. print_r($authSession->getUser()->getData(), true));

                // Log back trace
                $e = new \Exception();
                $logger->info($e->getTraceAsString());
                $logger->info('End debugging!'); // Print string type data
                return [];
                // Log request
            }   catch (\Exception $e) {

                $logger->info($e->getMessage());
                $logger->info('End debugging!'); // Print string type data
            }
        }
        return [];

    }
}
