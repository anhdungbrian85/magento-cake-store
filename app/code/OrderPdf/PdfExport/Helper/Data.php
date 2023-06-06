<?php

namespace OrderPdf\PdfExport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider;
use X247Commerce\Checkout\Model\Config\DeliveryConfigProvider;

class Data extends AbstractHelper
{

    const PICKUP_SHIPPING_METHOD = 1;

    const DELIVERY_SHIPPING_METHOD = 2;

    protected $directory;
    protected $catalogImageHelper;

    protected $productRepository;

    protected $orderRepository;

    protected $timezone;

    protected $timeHandler;

    protected $catalogProductTypeConfigurable;

    protected $storeManager;

    protected $deliveryDateProvider;

    protected $assetRepo;

    protected $fileFactory;

    protected $swatchHelper;

    protected $addressConfig;

    public function __construct(
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider $deliveryDateProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $catalogImageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        TimeHandler $timeHandler,
        TimezoneInterface $timezone,
        OrderRepositoryInterface $orderRepository,
        \Magento\Swatches\Helper\Data $swatchHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->addressConfig = $addressConfig;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
        $this->timeHandler = $timeHandler;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productRepository = $productRepository;
        $this->catalogImageHelper = $catalogImageHelper;
        $this->storeManager = $storeManager;
        $this->deliveryDateProvider = $deliveryDateProvider;
        $this->assetRepo = $assetRepo;
        $this->directory = $directory;
        $this->fileFactory = $fileFactory;
        $this->swatchHelper = $swatchHelper;
    }

    public function createOrderPdf($order, $_fileFactory)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/order_pdf.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Start debugging!');
        $orderData = $this->getOrderData($order);
        $itemsData = $this->getOrderItemData($order);
        $logger->info('Before check empty order data!');
        if (empty($orderData)) {
            return;
        }

        try {
            $logger->info('After check empty order data!');
            $orderItemsDetailHtml = '';
            $orderNoteDetailHtml = '';
            $currencySymbol = $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $logger->info('Before check empty items data!');
            $delivery = $this->deliveryDateProvider->findByOrderId($orderData['order_id']);
            $deliveryOrderHtml = '';
            if ($orderData['delivery_type'] == self::DELIVERY_SHIPPING_METHOD) {
                $time =  $delivery->getData('time');
                $date = $delivery->getData('date');
                $dateInWeek = (new \DateTime($date))->format('w');
                $isWeekend = ($dateInWeek == 0 || $dateInWeek == 6);
                $deliveryTime = DeliveryConfigProvider::WEEKDAY_DELIVERY_TIMESLOT;

                if ($isWeekend && $time == DeliveryConfigProvider::WEEKEND_DELIVERY_TIME_START) {
                    $deliveryTime = DeliveryConfigProvider::WEEKEND_DELIVERY_TIMESLOT;
                }
                
                
                $shippingAddress = $order->getShippingAddress();
                $streetData = $shippingAddress->getStreet();
                $shippingAddressHtml = $streetData[0] . ', ' . $shippingAddress->getPostcode();
                $deliveryOrderHtml = "
                        <div class='order-date-title' style='margin-top: 10px'><span class='text-size-10 text-bold'>Delivery Date</span>: <span class='text-size-10 text-size-20'>{$this->timezone->formatDate($delivery->getData('date'), \IntlDateFormatter::FULL, false)}</span></div>
                        <div class='order-time-title' style='margin-top: 10px'><span class='text-size-10 text-bold'>Delivery Time</span>: <span class='text-size-10 text-size-20'>{$deliveryTime}</span></div>
                        <div class='order-time-title' style='margin-top: 10px'><span class='text-size-10 text-bold'>Delivery Address</span>: <span class='text-size-10'>{$shippingAddressHtml}</span></div>
                        ";
            } else {
                $deliveryOrderHtml = "
                    <div class='order-date-title' style='margin-top: 10px'><span class='text-size-10 text-bold'>Date</span>: <span class='text-size-20'>{$orderData['delivery_date']}</span></div>
                    <div class='order-time-title' style='margin-top: 10px'><span class='text-size-10 text-bold'>Time</span>: <span class='text-size-20'>{$orderData['delivery_time']}</span></div>";
            }
            if (isset($itemsData) && $itemsData != null) {
                $logger->info('During check empty items data!');
                $tmp = 0;
                foreach ($itemsData as $item) {
                    $tmp++;
                    $product = $this->productRepository->get($item->getSku());
                    $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
                    $sku = $item->getSku();
                    $imageUrl = $this->catalogImageHelper->init($product, 'product_thumbnail_image')->getUrl();
                    if (isset($parentByChild[0])) {
                        $parentProduct = $this->productRepository->getById($parentByChild[0]);
                        $sku = $parentProduct->getSku();
                        $imageUrl = $this->catalogImageHelper->init($parentProduct, 'product_thumbnail_image')->getUrl();
                    }
                    $shape = $item->getProduct()->getAttributeText('shape') ? $item->getProduct()->getAttributeText('shape') : " ";
                    $sponge = $product->getAttributeText('sponge') ? $product->getAttributeText('sponge') : " ";
                    $size_serving = $product->getAttributeText('size_servings') ? $product->getAttributeText('size_servings') : " ";
                    switch ($sponge) {
                        case 'Victoria Base':
                            $base = 'VS';
                            break;
                        case 'Red Velvet Base':
                            $base = 'RV';
                            break;
                        case 'Mango Base':
                            $base = 'MB';
                            break;
                        case 'Chocolate Base':
                            $base = 'CH';
                            break;
                        case 'Caramel Base':
                            $base = 'CA';
                            break;
                    }
                    $size = str_replace('"'," ",substr($size_serving, 0, 3)); // 10 6
                    $colour = $product->getAttributeText('color') ? $product->getAttributeText('color') : "";
                    $colorOption = $product->getResource()->getAttribute('color')->getSource()->getSpecificOptions($product->getData('color'));
                    $colourOptionId = 0;
                    $colorHexCode = '';
                    if (!empty($colorOption)) {
                        foreach ($colorOption as $colorOptionItem) {
                            if ($colorOptionItem['value']) {
                                $colourOptionId = $colorOptionItem['value'];
                            }
                        }
                    }
                    if ($colourOptionId > 0) {
                        $colorData = $this->swatchHelper->getSwatchesByOptionsId([$colourOptionId]);
                        if (!empty($colorData['value'])) {
                            $colorHexCode = $colorData['value'];
                        }
                    }

                    $options = $item->getProductOptions() ? $item->getProductOptions() : " "; //custom options value
                    $orderPath = [];
                    $orderPath['message'] = '';
                    $orderPath['photo'] = '';
                    $orderPath['number_shape'] = '';
                    $orderPath['number'] = '';
                    $logger->info('Before check empty options!');
                    if (isset($options['options']) && !empty($options['options'])) {
                        foreach ($options['options'] as $option) {
                            $response = $this->isJson('' . $option['option_value'] . '', true);
                            if (!empty($response) && !empty($response->fullpath)) {
                                $orderPath['photo'] = $response->fullpath;
                            } else {
                                if ($option['label'] == 'Personalised Message On Cake') {
                                    $orderPath['message'] = $option['option_value'];
                                } else {
                                    if ($option['label'] == 'Number') {
                                        $orderPath['number'] = $option['value'];
                                    } else {
                                        if ($option['label'] == 'Number Shape') {
                                            $orderPath['number_shape'] = $option['value'];
                                        }
                                    }
                                }

                            }
                        }
                    }
                    $colorHtml = $colour ? "<div class='colour-wrapper'>
                                                <p class='colour-view' >
                                                    <span style='background-color:{$colorHexCode};height:15px;width:15px;border: 1px solid {$colorHexCode}; color:{$colorHexCode};border-radius: 25px;'>____</span>
                                                </p>
                                                <div class='colour-text' style='margin-top: 50px;'>{$colour}</div>
                                            </div>" : '';
                    if ($orderPath['number']) {
                        $orderNumberHtml = "<td class='grey-border'>{$orderPath['number']}</td>";
                    } else {
                        $orderNumberHtml = "<td class='grey-border'>{$orderPath['number_shape']}</td>";
                    }
                    $logger->info('After check empty options!');
                    $logger->info('Before render order info!');
                    $iconShapeHtml = '';
                    if ($shape != 'Number') {
                        $iconShape = "OrderPdf_PdfExport::images/{$shape}.png";
                        $iconShapeHtml = "<img class='shape-icon' style='vertical-align: top'
                                                src='{$this->assetRepo->getUrlWithParams($iconShape, [])}?t=png' width='80' /><br>{$shape}<br>{$orderPath['number_shape']}";
                    } else {
                        if ($orderPath['number_shape']) {
                            $iconShapeHtml = "{$shape}<br>{$orderPath['number_shape']}";
                        } else {
                            $iconShapeHtml = "{$shape}<br>{$orderPath['number']}";
                        }
                    }
                    $itemHtml = "
                    <div class='order-info'>
                        <div class='order-info-image'><img class='order-info-image-icon' style='vertical-align: top' src='{$imageUrl}?t=jpg' /></div>
                        <div class='order-info-content'>
                            <div style='margin-top: 10px;' class='order-number-title'><span class='text-bold text-size-10'>Order number</span>: <span class='text-size-20'>{$orderData['order_no']}</span></div>"
                        . $deliveryOrderHtml . "
                            <div style='margin-top: 10px;'><span class='text-bold text-size-10'>Billing Name</span>     : {$orderData['firstname']} {$orderData['lastname']}</div>
                            <div style='margin-top: 10px;'><span class='text-bold text-size-10'>Billing Tel</span>      : {$orderData['phone_no']}</div>
                            <div style='margin-top: 10px;'><span class='text-bold text-size-10'>Billing Email</span>    : {$orderData['email']}</div>
                        </div>
                    </div>
                    <table class='item-table'>
                        <tr>
                            <td class='grey-border'>Ref</td>
                            <td class='grey-border'>Pic</td>
                            <td class='grey-border'>Base</td>
                            <td class='grey-border'>Shape</td>
                            <td class='grey-border'>Size</td>
                            <td class='grey-border'>Number</td>
                            <td class='grey-border'>Colour</td>
                        </tr>
                        <tr>
                            <td class='grey-border'>{$sku}</td>" . ((!empty($orderPath['photo'])) ? '<td class="grey-border">[Custom]</td>' : '<td class="grey-border">[No Custom]</td>') . "
                            <td class='grey-border'>{$base}</td>
                            <td class='grey-border'>
                                {$iconShapeHtml}
                            </td>
                            <td class='grey-border'>{$size}</td>
                            {$orderNumberHtml}
                            <td class='grey-border'>{$colorHtml}</td>
                        </tr>
                </table>";
                    $orderItemsDetailHtml .= $itemHtml;
                    $logger->info('After render order info!');
                    $logger->info('Before render message container!');
                    $itemMessageHtml = "<div class='message-container grey-border'>
                    <div class='message-title'>Message</div>
                    <div class='message-content'>{$orderPath['message']}</div>
                </div>";
                    $orderItemsDetailHtml .= $itemMessageHtml;
                    $logger->info('After render message container!');
                    $logger->info('Before render photo container!');
                    $itemPhotoHtml = (!empty($orderPath['photo'])) ? "<div class='photo-container grey-border'>
                    <div class='photo-title'>Customer's Photo</div>
                    <div class='photo-content'>
                    <img class='customer-photo' style='vertical-align: top' src='{$orderPath['photo']}'/>
                    </div> </div>" : "<div class='photo-container grey-border'>
                    <div class='photo-title'>Customer's Photo</div>
                     </div>";

                    $orderItemsDetailHtml .= $itemPhotoHtml;
                    $logger->info('After render photo container!');
                    $logger->info('Before render note container!');

                    if ($delivery->getId()) {
                        $orderNoteDetailHtml = "<div class='note-container grey-border'>
                            <div class='note-title'>Notes:</div>
                            <div class='note-content'>{$delivery->getData('comment')}</div>
                        </div>";
                    }
                    $logger->info('After render note container!');
                    $orderItemsDetailHtml .= $orderNoteDetailHtml;
                    $orderData['grand_total'] = number_format($orderData['grand_total'], 2, '.', ',');
                    $orderBillingDetailHtml = "
                    <div class='order-total-container'>
                        <div class='order-total-item grey-border'>
                            Received as per order<br/>
                            Print Name: -----------<br/>
                            Signature:  -----------<br/>
                            Date:       -----------<br/>
                        </div>
                        <div class='order-total-item'>
                                Made By:    -----------<br/>
                                Serve By:   -----------<br/>
                                <div class='grey-border'>
                                    <div>{$currencySymbol}{$orderData['grand_total']} Order</div>
                                    <div>{$currencySymbol}{$orderData['grand_total']} Paid Online</div>
                                </div>
                        </div>
                    </div>
               ";
                    $orderItemsDetailHtml .= $orderBillingDetailHtml;
                    $itemBarCodeHtml = "<div>
                    <div class='barcode-content'><barcode type='EAN128A' code='{$product->getBarcode()}' text='1' class='' /></div>
                </div>";
                    $orderItemsDetailHtml .= $itemBarCodeHtml;
                    if (count($itemsData) > $tmp) {
                        $orderItemsDetailHtml .= '<pagebreak />';
                    }

                }
            }
            $colorHex = $colour ? $this->getColorCss($colour) : '';
            $colorStyle = ".colour-wrapper {width:max-content;}
                            .colour-view {background:{$colorHex};height:25px;width:25px;border-radius: 25px;margin:auto;}
                            .colour-text {margin-top: 5px;}";
            $cssColor = $colour ? $colorStyle : '';
            $html = "
            <style>
                td { padding: 2px;}
                h1 { margin-bottom: 0; }
                .order-info-content {float: left;}
                .order-info-image {float: left;}
                .grey-border {border: 1px solid grey; margin-top: 2px; margin-bottom: 2px;}
                .text-bold {font-weight: bold !important;}
                .text-size-10 {font-size: 14px;}
                .text-size-20 {font-size: 20px}
                .order-total-container { width: 100%;}
                .order-total-item {width: 48%; float:left}
                .order-info {width: 100%; padding-left: 10px;}
                .order-info tr {display: flex; width: 100%;}
                .order-info .order-info-image {width: 30%; text-align: center}
                .order-info-image-icon {width: 120px;}
                .item-table {width: 100%;}
                .item-table td {width: auto;}
                .note-container {width: 100%; padding: 5px;}
                .message-container {width: 100%; padding: 5px;}
                .photo-container {width: 100%; padding: 5px;}
                .photo-container .photo-content {text-align: center;}
                .barcode-container {padding: 10px;}
                .shape-icon {width: 35px;}
                .customer-photo {width: 100px;}
                .barcode-content {margin-top: 2px;}
                {$cssColor}
            </style>
            <div class='content-container'>
                {$orderItemsDetailHtml}
                <br />
            </div>";
            $mpdf = new \Mpdf\Mpdf([
                'tempDir' => $this->directory->getPath('var') . '/log/tmp/mpdf',
                'margin_left' => 10,
                'margin_right' => 5,
                'margin_top' => 25,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 10,
                'showBarcodeNumbers' => FALSE,
                'default_font' => 'dejavusanscondensed',
                'format' => 'A5'
            ]);
            try {
                $logger->info('Start render order pdf!');
                $mpdf->SetHTMLFooter('<div style="text-align: left; font-weight: bold; color:purple;">PAGE {PAGENO} of {nbpg}</div>');
                $mpdf->WriteHTML($html);
                $mpdf->Output($orderData['order_no'] . '.pdf', 'D');
                $fileContent = ['type' => 'string', 'value' => $mpdf->Output($orderData['order_no'] . '.pdf', 'S'), 'rm' => true];
                return $this->fileFactory->create(
                    $orderData['order_no'] . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } catch (\Mpdf\MpdfException $e) {
                $logger->info('Has error when renderring order pdf:' . $e->getMessage());
            }

            $logger->info('End debugging!');
        } catch (\Exception $e) {
            $logger->info('Has error when processing:' . $e->getMessage());
            return $e;
        }
    }

    protected function getItemOptions($item)
    {
        $options = $item->getProductOptions() ? $item->getProductOptions() : " ";
        if ($options) {
            if (isset($options['options'])) {
                $result[] = $options['options'];
            }
            if (isset($options['additional_options'])) {
                $result[] = $options['additional_options'];
            }
            if (isset($options['attributes_info'])) {
                $result[] = $options['attributes_info'];
            }
        }
        return array_merge([], ...$result);
    }

    public function getOrderData($orderobj)
    {
        $amastyOrderEntity = $this->orderRepository->getByOrderId($orderobj->getId());
        $orderDetails = [
            'order_id' => $orderobj->getId(),
            'order_no' => $orderobj->getData('increment_id'),
            'date_time' => $orderobj->getData('created_at'),
            'firstname' => $orderobj->getData('customer_firstname'),
            'lastname' => $orderobj->getData('customer_lastname'),
            'email' => $orderobj->getData('customer_email'),
            'phone_no' => $orderobj->getBillingAddress()->getTelephone(),
            'delivery_date' => $this->timezone->formatDate($amastyOrderEntity->getDate(), \IntlDateFormatter::FULL, false),
            'delivery_time_from' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeFrom()),
            'delivery_time_to' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeTo()),
            'delivery_time' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeFrom()) . ' - ' . $this->timeHandler->convertTime($amastyOrderEntity->getTimeTo()),
            'grand_total' => $orderobj->getGrandTotal(),
            'delivery_type' => ($orderobj->getShippingMethod() == 'amstorepickup_amstorepickup') ? self::PICKUP_SHIPPING_METHOD : self::DELIVERY_SHIPPING_METHOD
        ];
        return $orderDetails;
    }

    public function getOrderItemData($orderobj)
    {
        $orderItems = [];
        $orderItems = $orderobj->getAllVisibleItems();
        // echo count($orderItems);
        return $orderItems;
    }

    public function isJson($string, $return_data = false)
    {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }

    public function getOptionIdByLabel($product, $attributeCode, $optionLabel)
    {
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }

    public function getColorCss($colour)
    {
        switch ($colour) {
            case 'Baby Blue':
                $cssColor = '#3ca9ed';
                break;
            case 'Green':
                $cssColor = '#008000';
                break;
            case 'Lilac':
                $cssColor = '#c5aded';
                break;
            case 'Pink':
                $cssColor = '#ffc0cb';
                break;
            case 'Red':
                $cssColor = '#ff0000';
                break;
            case 'Yellow':
                $cssColor = '#ffff00';
                break;
            case 'Tangerine_Apricot':
            case 'Tangerine Apricot':
                $cssColor = '#d13913';
                break;
            case 'Rose Gold':
                $cssColor = '#b76e79';
                break;
            case 'Silver':
                $cssColor = '#bfb9bf';
                break;
            case 'Mixed Blue / Pink':
            case 'Mixed  Blue / Pink':
                $cssColor = '#bfb9bf';
                break;
            case 'Chocolate':
                $cssColor = '#a65c17';
                break;
            case 'Blue':
                $cssColor = '#0000ff';
                break;

            default:
                $cssColor = '';
                break;
        }
        return $cssColor;
    }
}