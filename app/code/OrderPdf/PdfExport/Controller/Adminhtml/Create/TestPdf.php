<?php
namespace OrderPdf\PdfExport\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class TestPdf extends \Magento\Backend\App\Action
{

    protected $directoryList;

    public function __construct(
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        Context $context
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test_order_pdf.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Start debugging!');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $mpdf = new \Mpdf\Mpdf([
            'tempDir' =>  $this->directoryList->getPath('media') . '/tmp/mpdf',
            'margin_left' => 10,
            'margin_right' => 5,
            'margin_top' => 25,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'showBarcodeNumbers' => FALSE
        ]);
        try {
            $mpdf->WriteHTML('Hello World');
            $mpdf->Output('test_order_pdf.pdf', 'I');
        } catch (\Exception $e) {
            $logger->info('Has error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $logger->info('End debugging!');
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
