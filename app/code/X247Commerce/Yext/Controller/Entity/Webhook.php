<?php
/**
 * @author Phung Thong <phung.thong@247commerce.co.uk>
 * @copyright 2022 247Commerce
 */

namespace X247Commerce\Yext\Controller\Entity;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\ActionInterface;
use X247Commerce\Yext\Model\YextAttribute;

class Webhook extends Action implements ActionInterface
{

    protected LoggerInterface $logger;
    protected RawFactory $rawFactory;
    protected YextAttribute $yextAttribute;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        RawFactory $rawFactory,
        YextAttribute $yextAttribute
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->rawFactory = $rawFactory;
        $this->yextAttribute = $yextAttribute;
    }

    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->rawFactory->create()
                        ->setContents('You don\'t have permission to access this resource!');
        }
        $body = file_get_contents("php://input");
        $events = json_decode($body, true);

        if (!empty($events['meta']['eventType']) && $events['meta']['eventType'] == 'ENTITY_DELETED') {
            $location = $this->deleteLocation($events['entityId']);
        }

        if (!empty($events['meta']['eventType']) && $events['meta']['eventType'] == 'ENTITY_UPDATED') {
            
            $location = $this->editLocation($events, $events['entityId']);
        }

        $this->logger->log('600', $body);
        $this->logger->log('600', print_r($_SERVER, true));
        $raw = $this->rawFactory->create();
        
        if (!empty($location)) {
            $raw->setContents('Your webhook has been handled successfully!'. $events['meta']['eventType']);
        }   else {
            $raw->setContents('Your webhook has been handled successfully!');
        }
        
        return $raw;
    }

    public function deleteLocation($yexyEntityId)
    {
        return $this->yextAttribute->deleteLocation($yexyEntityId);
    }

    public function editLocation($data, $yexyEntityId)
    {
        return $this->yextAttribute->editLocation($data, $yexyEntityId);
    }
}
