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

class Webhook extends Action implements ActionInterface
{

    protected LoggerInterface $logger;
    protected RawFactory $rawFactory;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        RawFactory $rawFactory

    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->rawFactory = $rawFactory;
    }

    public function execute()
    {
        $body = file_get_contents("php://input");
        $events = json_decode($body, true);

        $this->logger->log('600', $body);
        $this->logger->log('600', print_r($_SERVER, true));
        $raw = $this->rawFactory->create();
        $raw->setContents('Your webhook has been handled successfully!');
        return $raw;
    }
}
