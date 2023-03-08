<?php

namespace X247Commerce\Checkout\Plugin\Checkout\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\DateTimeValidator;
use Amasty\StorePickupWithLocator\Model\Quote\CurbsideValidator;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\GuestShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Quote\Model\QuoteRepository;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestShippingInformationManagementPlugin for save store pickup data
 * @todo encapsulate logic
 */
class GuestShippingInformationManagementPlugin
{

    /**
     * @var DateTimeValidator
     */
    private $validator;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CurbsideValidator
     */
    private $curbsideValidator;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    protected $storeLocationContextInterface;

    protected $storeLocationContext;
    protected $quoteIdMaskFactory;

    public function __construct(
        QuoteRepository $quoteRepository,
        DateTimeValidator $validator,
        ShippingAddressManagementInterface $shippingAddressManagement,
        ConfigProvider $configProvider,
        CurbsideValidator $curbsideValidator,
        TimeHandler $timeHandler,
        StoreLocationContextInterface $storeLocationContext,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->configProvider = $configProvider;
        $this->curbsideValidator = $curbsideValidator;
        $this->timeHandler = $timeHandler;
        $this->storeLocationContext = $storeLocationContext;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * Save pickup data
     *
     * @param ShippingInformationManagement $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param string|int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return PaymentDetailsInterface
     */
    public function afterSaveAddressInformation(
        GuestShippingInformationManagementInterface $subject,
        $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $pickupQuoteData = $addressInformation->getExtensionAttributes()->getAmPickup();

        if (!($pickupQuoteData instanceof QuoteInterface)) {
            return $paymentDetails;
        }
        
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteEntity = $this->quoteRepository->getActive($quoteIdMask->getQuoteId());
        
        $storeLocationId = $pickupQuoteData->getStoreId();
        $quoteEntity->setStoreLocationId($storeLocationId);

        $this->quoteRepository->save($quoteEntity);
        $this->storeLocationContext->setStoreLocationId($storeLocationId);
        
        $shippingMethod = $addressInformation->getShippingCarrierCode();
        if ($shippingMethod == 'amstorepickup') {
        	$this->storeLocationContext->setDeliveryType(0);
        }	else {
        	$this->storeLocationContext->setDeliveryType(1);
        }
        return $paymentDetails;
    }
}
