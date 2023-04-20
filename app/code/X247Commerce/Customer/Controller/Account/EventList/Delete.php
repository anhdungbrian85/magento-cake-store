<?php
namespace X247Commerce\Customer\Controller\Account\EventList;

class Delete extends \Magento\Framework\App\Action\Action
{
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\X247Commerce\Customer\Model\EventFactory $eventFactory,
		\Magento\Framework\App\Request\Http $request,
		\Psr\Log\LoggerInterface $logger
	)
	{
		$this->request = $request;
		$this->eventFactory = $eventFactory;
		$this->_logger = $logger;
		return parent::__construct($context);
	}

	public function execute()
	{
		try {
			$evenId = $this->request->getParam('id');
			$this->eventFactory->create()->load($evenId)->delete();
			$this->messageManager->addSuccessMessage(__('Delete event successful'));
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage(__('The Event was unable to be delete. Please try again.'));
			$this->_logger->error($e->getMessage()); 
		}
		
		return $this->resultRedirectFactory->create()->setPath('*/*/');
	}
}