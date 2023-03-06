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
        $this->resultRawFactory = $resultRawFactory;
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
		$this->_orderpdf = $orderpdf;
		$this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        parent::__construct($context,$coreRegistry,$fileFactory,$translateInline,$resultPageFactory,
		$resultJsonFactory,$resultLayoutFactory,$resultRawFactory,$orderManagement,$orderRepository,$logger );
    }
    public function execute()
    {
		$_fileFactory = $this->_fileFactory;
        // In case you want to do something with the order
        $order_id = $this->getRequest()->getParam('order_id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //$order = $this->_initOrder();
		$order = $this->_orderpdf->load($order_id);

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // TODO: Do something with the order
			if($order->getState()=='complete' || $order->getState()=='processing'){

			$this->helper->createOrderPdf($order,$_fileFactory);

			}else{

				$this->messageManager->addSuccessMessage(__('Order status is not in processing or completed.'));
			}

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $resultRedirect->setPath('sales/order/view', [ 'order_id' => $order->getId() ]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('OrderPdf_PdfExport::order_dosomething');
    }
}
