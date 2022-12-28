<?php

namespace X247Commerce\Checkout\Plugin\Checkout\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\DateTimeValidator;
use Amasty\StorePickupWithLocator\Model\Quote\CurbsideValidator;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\Quote\Model\QuoteRepository as QuoteMageRepository;

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

    protected $quoteMageRepository;

    public function __construct(
        QuoteRepository $quoteRepository,
        DateTimeValidator $validator,
        ShippingAddressManagementInterface $shippingAddressManagement,
        ConfigProvider $configProvider,
        CurbsideValidator $curbsideValidator,
        TimeHandler $timeHandler,
        QuoteMageRepository $quoteMageRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->configProvider = $configProvider;
        $this->curbsideValidator = $curbsideValidator;
        $this->timeHandler = $timeHandler;
        $this->quoteMageRepository = $quoteMageRepository;
    }

    /**
     * Validate pickup data
     *
     * @param ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return null
     * @throws InputException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        if ($addressInformation->getShippingCarrierCode() !== Shipping::SHIPPING_METHOD_CODE) {
            return null;
        }

        $pickupQuoteData = $addressInformation->getExtensionAttributes()->getAmPickup();

        $addressId = $this->shippingAddressManagement->get($cartId)->getId();
        $quoteEntity = $this->quoteRepository->getByAddressId($addressId);

        $quoteMage = $this->quoteMageRepository->getActive($cartId);
        // var_dump($cartId);die();
        // var_dump($quoteMage->getStoreLocationId());die();
        // var_dump($quoteEntity->getStoreId());die();
        if ($pickupQuoteData instanceof QuoteInterface) {
            // var_dump($pickupQuoteData->getStoreId());
            // var_dump($quoteMage->getStoreLocationId());
            $storeValue = !empty($pickupQuoteData->getStoreId()) ? (int)$pickupQuoteData->getStoreId() : (int)$quoteMage->getStoreId();

            // var_dump($storeValue);die();
            // var_dump($cartId);die();
            // // $quote = $this->quoteRepository->get($cartId);
            // var_dump($quote->getData('store_location_id'));die();
            // var_dump($this->storeLocationContextInterface->getStoreLocationId());die();
            // var_dump(empty($storeValue));die();
            // var_dump(get_class_methods($pickupQuoteData));die();
            // var_dump($pickupQuoteData->getStoredData());die();

            if (!$storeValue) {
                throw new InputException(__('Store ID is not specified. Please, choose a store for pickup.'));
            }

            if ($this->configProvider->isPickupDateEnabled()) {
                $dateValue = (string)$pickupQuoteData->getDate();
                $timeFrom = (int)$pickupQuoteData->getTimeFrom();
                $timeTo = (int)$pickupQuoteData->getTimeTo();

                if (!$this->validator->isValidDate($cartId, $storeValue, $dateValue, $timeFrom, $timeTo)) {
                    throw new InputException(__('Store Pickup Date/Time is not valid.'));
                }

                $pickupQuoteData->setDate($this->timeHandler->prepareDateFormat($dateValue));
            }

            $this->curbsideValidator->validateComment($pickupQuoteData);

            return null;
        } else {
            throw new InputException(__('Pickup data is empty. Please, specify pickup info.'));
        }
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
        ShippingInformationManagement $subject,
        $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $pickupQuoteData = $addressInformation->getExtensionAttributes()->getAmPickup();

        if ($addressInformation->getShippingCarrierCode() !== Shipping::SHIPPING_METHOD_CODE
            || !($pickupQuoteData instanceof QuoteInterface)
        ) {
            return $paymentDetails;
        }

        $addressId = $this->shippingAddressManagement->get($cartId)->getId();
        $quoteEntity = $this->quoteRepository->getByAddressId($addressId);
        $timeFrom = (int)$pickupQuoteData->getTimeFrom();
        $timeTo = (int)$pickupQuoteData->getTimeTo();
        $date = (string)$pickupQuoteData->getDate();
        $isCurbside = $this->curbsideValidator->shouldSaveCurbsideValue($pickupQuoteData)
            ? $pickupQuoteData->getIsCurbsidePickup()
            : false;
        $comment = $this->curbsideValidator->shouldSaveComment($pickupQuoteData)
            ? $pickupQuoteData->getCurbsidePickupComment()
            : '';

        $quoteEntity
            ->setAddressId($addressId)
            ->setQuoteId($cartId)
            ->setStoreId((int)$pickupQuoteData->getStoreId())
            ->setDate($date ?: null)
            ->setTimeFrom($timeFrom ?: null)
            ->setTimeTo($timeTo ?: null)
            ->setIsCurbsidePickup($isCurbside)
            ->setCurbsidePickupComment($comment);

        $this->quoteRepository->save($quoteEntity);

        return $paymentDetails;
    }
}
