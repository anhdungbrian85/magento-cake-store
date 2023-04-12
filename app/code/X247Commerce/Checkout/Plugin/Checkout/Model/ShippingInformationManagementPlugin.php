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
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\QuoteIdMaskFactory;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;


/**
 * Class ShippingInformationManagementPlugin for save store pickup data
 * @todo encapsulate logic
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

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
    protected $quoteIdMaskFactory;
    protected $storeLocationContext;

    public function __construct(
        QuoteRepository $quoteRepository,
        DateTimeValidator $validator,
        ShippingAddressManagementInterface $shippingAddressManagement,
        ConfigProvider $configProvider,
        CurbsideValidator $curbsideValidator,
        TimeHandler $timeHandler,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        StoreLocationContextInterface $storeLocationContext
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->configProvider = $configProvider;
        $this->curbsideValidator = $curbsideValidator;
        $this->timeHandler = $timeHandler;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->storeLocationContext = $storeLocationContext;
    }

    /**
     * Save pickup data
     *
     * @param ShippingInformationManagementInterface $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param string|int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return PaymentDetailsInterface
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $pickupQuoteData = $addressInformation->getExtensionAttributes()->getAmPickup();
        $shippingMethod = $addressInformation->getShippingCarrierCode();
        $quoteEntity = $this->quoteRepository->getActive($cartId);

        if ($shippingMethod == 'amstorepickup') {
            if ($pickupQuoteData instanceof QuoteInterface) {
                $storeLocationId = $pickupQuoteData->getStoreId();
                if ($storeLocationId != $this->storeLocationContext->getStoreLocationId()) {
                    
                    $quoteEntity->setStoreLocationId($storeLocationId);
                    $this->storeLocationContext->setDeliveryType($quoteEntity->getData('delivery_type'));
                    $this->quoteRepository->save($quoteEntity);
                    $this->storeLocationContext->setStoreLocationId($storeLocationId);
                }
            }
        }   else {
            $quoteEntity->setData('delivery_type', 1);
            $this->quoteRepository->save($quoteEntity);
            $this->storeLocationContext->setDeliveryType(1);
        }

        return $paymentDetails;
    }
}
