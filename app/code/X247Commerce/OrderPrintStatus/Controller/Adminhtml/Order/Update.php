<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\OrderPrintStatus\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

class Update extends Action implements HttpPostActionInterface 
{

    protected LoggerInterface $logger;
    protected OrderFactory $orderFactory;
 
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        OrderFactory $orderFactory

    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Execute 
     *
     * @return json
     */
    public function execute()
    {
        

        try {
            $orderId = $this->getRequest()->getParam('order_id');

            $currentPrintStatus = $this->getRequest()->getParam('current_print_status');
            $switchedStatus = $currentPrintStatus ? 0 : 1;

            $order = $this->orderFactory->create()->load($orderId);
            $order->setdata('print_status', $switchedStatus)->save();

            return $this->jsonResponse(
                ['success' => true]
            );

        } catch (LocalizedException $e) {
            return $this->jsonResponse(
                ['success' => false, 'message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse(
                ['success' => false, 'message' => $e->getMessage()]
            );
        }
    }

    protected function jsonResponse($response)
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($response);
    }

  
}
