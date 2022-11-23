<?php

namespace X247Commerce\Sales\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\Storelocator\Model\Location;

class SendMailOnOrderSuccess implements ObserverInterface
{
	protected $_inlineTranslation;

	protected $_transportBuilder;

	protected $storeManager;

    protected $location;

    protected $scopeConfig;

	public function __construct(
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		StoreManagerInterface $storeManager,
		Location $location,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->_inlineTranslation = $inlineTranslation;
		$this->_transportBuilder = $transportBuilder;
		$this->storeManager = $storeManager;
		$this->location = $location;
		$this->scopeConfig = $scopeConfig;
	}

	/**
	 * @param \Magento\Framework\Event\Observer $observer
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$locationId = $order->getStoreLocationId();
		if (!empty($locationId)) {
			$locationCollection = $this->location->load($locationId);
			$email = $locationCollection->getEmail();
			if (!empty($email)) {
				return $this->sendEmail($email);
			}
		}
	}

	private function sendEmail($email)
	{
		try
        {
            $this->_inlineTranslation->suspend();
            $sender = [
                'name' => $this->getStorename(),
                'email' => $this->getStoreEmail()
            ];

            $transport = $this->_transportBuilder
            ->setTemplateIdentifier('after_order_email_template_staff_location')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
                )
                ->setTemplateVars([
                    'order' => 2
                ])
                ->setFrom($sender)
                ->addTo($email)
                ->getTransport();

            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch(\Exception $e){
            
        }
	}

	private function getStorename()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

 	private function getStoreEmail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}