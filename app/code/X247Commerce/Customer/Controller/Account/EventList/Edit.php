<?php
namespace X247Commerce\Customer\Controller\Account\EventList;

use X247Commerce\Customer\Cron\SentMailAlertEvent;

class Edit extends \Magento\Framework\App\Action\Action
{
	public $request;
	public $eventFactory;
	public $SentMailAlertEvent;
	public $configValue;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		SentMailAlertEvent $SentMailAlertEvent,
		\Magento\Framework\App\Request\Http $request,
		\X247Commerce\Customer\Model\EventFactory $eventFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $configValue
	)
	{
		$this->SentMailAlertEvent = $SentMailAlertEvent;
		$this->configValue = $configValue;
		$this->request = $request;
		$this->eventFactory = $eventFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
		try {
			$formKey = $this->getRequest()->getParam('form_key');
			if (!$this->formKeyValidator->validate($formKey)) {
				$this->messageManager->addErrorMessage(__('Please refresh the page and try again!.'));
			}else{
				$array = [];
				$data = $this->request->getParams();

				if (!$data) {
					$this->messageManager->addErrorMessage(__('Please choose the Event to Edit.'));
				} else {
					$array['occasion'] = $data['occasion'];
					$array['their_name'] = $data['name'];
					$array['date'] =  $data['year'] . '-' . $data['month'] . '-' . $data['day'];
					$array['customer_id'] = $data['customer_id'];
					
					if (!in_array("", $array)) {
						$eventFactory = $this->eventFactory->create();
						$valueConfigSendMail = $this->configValue->getValue(
							'x247commerce_customer/event/send_mail_save',
							\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
						);;
						
						if ($data['id'] != null) {
							$array['id'] = $data['id'];
							$eventFactory->setData($array)->save();
							if ($valueConfigSendMail) {
								$this->SentMailAlertEvent->execute(1, $array['id']);
							}
						} else {
							$eventFactory->setData($array)->save();
							if ($valueConfigSendMail) {
								$this->SentMailAlertEvent->execute(1, null, $array['customer_id']);
							}
						}
						
						$this->messageManager->addSuccessMessage(__('Save Event successful'));
					} else {
						$this->messageManager->addErrorMessage(__('The Event was unable to be Save. Please try again.'));
					}
				}
			}
			
		} catch (\Exception $e) {
		}

		return $this->resultRedirectFactory->create()->setPath('*/*/');
	}
}