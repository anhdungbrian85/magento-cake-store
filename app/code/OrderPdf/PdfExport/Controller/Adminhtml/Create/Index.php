<?php
namespace OrderPdf\PdfExport\Controller\Adminhtml\Create;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;
use OrderPdf\PdfExport\Helper\Data;

class Index extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Execute action
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
	 *
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
		\OrderPdf\PdfExport\Helper\Data $helper,
		\Magento\Sales\Model\Order $orderpdf

    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
		$this->_orderpdf = $orderpdf;
		$this->helper = $helper;
        parent::__construct($context,$coreRegistry,$fileFactory,$translateInline,$resultPageFactory,
		$resultJsonFactory,$resultLayoutFactory,$resultRawFactory,$orderManagement,$orderRepository,$logger );
    }
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/order_pdf.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Start debugging on Controller!');
		$_fileFactory = $this->_fileFactory;
        $order_id = $this->getRequest()->getParam('order_id');
		$order = $this->_orderpdf->load($order_id);
        $resultRedirect = $this->resultRedirectFactory->create();
        $logger->info('After get data on Controller');
        try {
            // TODO: Do something with the order
            $logger->info('Before check order status');
			if($order->getState()=='complete' || $order->getState()=='processing') {
                $logger->info('Before rendering order pdf on Controller');
			    $this->helper->createOrderPdf($order, $_fileFactory);
                $logger->info('After rendering order pdf on Controller');
			} else {
                $logger->info('Order status is not in processing or completed');
				$this->messageManager->addSuccessMessage(__('Order status is not in processing or completed.'));
			}

        } catch (\Exception $e) {
            $logger->info('Has error during process on Controller');
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        $logger->info('End debugging on Controller!');
        return $resultRedirect->setPath('sales/order/view', [ 'order_id' => $order->getId() ]);
    }
}
