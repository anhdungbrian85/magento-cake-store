<?php

namespace X247Commerce\Checkout\Plugin\Checkout\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\DateTimeValidator;
use Amasty\StorePickupWithLocator\Model\Quote\CurbsideValidator;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Amasty\StorePickupWithLocator\Plugin\Checkout\Model\ShippingInformationManagementPlugin;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\InputException;
use Magento\Quote\Model\ShippingAddressManagementInterface;


class StorePickupShippingInformationManagementPlugin extends ShippingInformationManagementPlugin
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

    public function __construct(
        QuoteRepository $quoteRepository,
        DateTimeValidator $validator,
        ShippingAddressManagementInterface $shippingAddressManagement,
        ConfigProvider $configProvider,
        CurbsideValidator $curbsideValidator,
        TimeHandler $timeHandler
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->validator = $validator;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->configProvider = $configProvider;
        $this->curbsideValidator = $curbsideValidator;
        $this->timeHandler = $timeHandler;
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

        if ($pickupQuoteData instanceof QuoteInterface) {
            $storeValue = (int)$pickupQuoteData->getStoreId();

            if (!$storeValue) {
                return null;
            }

            if ($this->configProvider->isPickupDateEnabled()) {
                $dateValue = (string)$pickupQuoteData->getDate();
                $timeFrom = (int)$pickupQuoteData->getTimeFrom();
                $timeTo = (int)$pickupQuoteData->getTimeTo();

                if (!$this->validator->isValidDate($cartId, $storeValue, $dateValue, $timeFrom, $timeTo)) {
                    return null;
                }

                $pickupQuoteData->setDate($this->timeHandler->prepareDateFormat($dateValue));
            }

            $this->curbsideValidator->validateComment($pickupQuoteData);

            return null;
        } else {
            return null;
        }
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $paymentDetails
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetailsInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
