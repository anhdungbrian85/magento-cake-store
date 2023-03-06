<?php
namespace X247Commerce\PopupAddtoCart\Controller\AddtoCart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\Controller\Result\RedirectFactory;

class Popup extends Action
{
    protected $_resultPageFactory;

    protected $_resultJsonFactory;

    protected $resultRedirectFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        RedirectFactory $resultRedirectFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $requestData = $this->getRequest()->getParams();
        $data = array();

        if ( isset( $requestData['product'] ) ) {
            $block = $resultPage->getLayout()
                    ->createBlock('X247Commerce\PopupAddtoCart\Block\AddtoCart\Popup')
                    ->setTemplate('X247Commerce_PopupAddtoCart::popup.phtml')
                    ->setData('productId',$requestData['product'])
                    ->toHtml();

            $result->setData(['output' => $block]);

            return $result;
        }


        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*');
        return $resultRedirect;
    }
}
