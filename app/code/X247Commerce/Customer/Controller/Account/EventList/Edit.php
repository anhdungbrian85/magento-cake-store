<?php
namespace X247Commerce\Customer\Controller\Account\EventList;

class Edit extends \Magento\Framework\App\Action\Action
{
	public $request;
	public $eventFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Request\Http $request,
		\X247Commerce\Customer\Model\EventFactory $eventFactory
	)
	{
		$this->request = $request;
		$this->eventFactory = $eventFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		$array = [];
		
		try {
			$data = $this->request->getParams();
			$array['occasion'] = $data['occasion'];
			$array['their_name'] = $data['name'];
			$array['date'] =  $data['year'] . '-' . $data['month'] . '-' . $data['day'];
			$array['customer_id'] = $data['customer_id'];
			
			if (!in_array("", $array)) {
				$eventFactory = $this->eventFactory->create();
				
				if ($data['id'] != null) {
					$array['id'] = $data['id'];
					$eventFactory->setData($array)->save();
				} else {
					$eventFactory->setData($array)->save();
				}

				$this->messageManager->addSuccessMessage(__('Save Event successful'));
			} else {
				$this->messageManager->addErrorMessage(__('The Event was unable to be Save. Please try again.'));
			}
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage(__('Please choose the Event to Edit.'));
		}

		return $this->resultRedirectFactory->create()->setPath('*/*/');
	}
}