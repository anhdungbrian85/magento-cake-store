<?php

namespace OrderPdf\PdfExport\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\StorePickupWithLocator\Model\TimeHandler;

class Data extends AbstractHelper
{

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

    public function __construct(
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
        Context $context
    ) {
        parent::__construct($context);
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
    }

    public function createOrderPdf($order,$_fileFactory)
    {
       $orderData = $this->getOrderData($order);
       $itemsData = $this->getOrderItemData($order);
       if (empty($orderData)) {
           return;
       }
       $orderItemsDetailHtml = '';
       $orderNoteDetailHtml = '';
       $currencySymbol = $this->storeManager->getStore()->getBaseCurrency()->getCurrencySymbol();
       $mediaUrl = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
        if(isset($itemsData) && $itemsData!=null) {
           foreach ($itemsData as $item) {
                $product = $this->productRepository->get($item->getSku());
                $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
                $sku = $item->getSku();
                if (isset($parentByChild[0])) {
                    $parentProduct = $this->productRepository->getById($parentByChild[0]);
                    $sku = $parentProduct->getSku();
                }
                $shape = $item->getProduct()->getAttributeText('shape') ? $item->getProduct()->getAttributeText('shape'):" ";
                $iconShape = "OrderPdf_PdfExport::images/{$shape}.png";
                $sponge = $product->getAttributeText('sponge') ? $product->getAttributeText('sponge'):" ";
                $size_serving = $product->getAttributeText('size_servings') ? $product->getAttributeText('size_servings'):" ";
                $base  = substr($sponge, 0, 1);//position,count V
                $size = str_replace('"'," ",substr($size_serving, 0, 3)); // 10 6
                $colour = $product->getAttributeText('color') ? $product->getAttributeText('color'):" ";
                $imageUrl = $this->catalogImageHelper->init($parentProduct, 'product_thumbnail_image')->getUrl();
               $options = $item->getProductOptions() ? $item->getProductOptions() : " ";//custom options value
               $orderPath = [];
               $orderPath['message'] = '';
               $orderPath['photo'] = '';
               $orderPath['number_shape'] = '';
               $orderPath['number'] = '';
               if (isset($options['options']) && !empty($options['options'])) {
                   foreach ($options['options'] as $option) {
                       $response = $this->isJson(''.$option['option_value'] . '', true);
                       if (!empty($response) && !empty($response->fullpath)) {
                           $orderPath['photo'] =  $response->fullpath;
                       } else {
                           if ($option['label'] == 'Personalised Message On Cake') {
                               $orderPath['message']  = $option['option_value'];
                           } else {
                               if ($option['label'] == 'Number') {
                                   $orderPath['number']  = $option['value'];
                               } else {
                                   if ($option['label'] == 'Number Shape') {
                                       $orderPath['number_shape']  = $option['value'];
                                   }
                               }
                           }

                       }
                   }
               }

                $itemHtml = "
                    <table>
                        <tr>
                        <td>Ref</td>
                        <td>Image</td>
                        <td>Base</td>
                        <td>Shape</td>
                        <td>Size</td>
                        <td>Colour</td>
                        <td>Number Shape</td>
                        <td>Number</td>
                    </tr>
                        <tr>
                        <td>{$sku}</td>
                        <td><img style='vertical-align: top' src='{$imageUrl}?t=jpg' width='80' /></td>
                        <td>{$base}</td>
                        <td><img style='vertical-align: top' src='{$this->assetRepo->getUrlWithParams($iconShape, [])}?t=png' width='80' /><br>{$shape}</td>
                        <td>{$size}</td>
                        <td>{$colour}</td>
                        <td>{$orderPath['number_shape']}</td>
                        <td>{$orderPath['number']}</td>
                    </tr>
                </table>";
                $orderItemsDetailHtml .= $itemHtml;


               $itemMessageHtml = "<div class='message-container'>
                    <div class='message-title'>Message</div>
                    <div class='message-content'>{$orderPath['message']}</div>
                </div>";
               $orderItemsDetailHtml .= $itemMessageHtml;

               $itemPhotoHtml = (!empty($orderPath['photo'])) ? "<div class='photo-container'>
                    <div class='photo-title'>Customer's Photo</div>
                    <div class='photo-content'>
                    <img style='vertical-align: top' src='{$orderPath['photo']}'/>
                   </div> </div>" : "<div class='photo-container'>
                    <div class='photo-title'>Customer's Photo</div>
                     </div>";

               $orderItemsDetailHtml .= $itemPhotoHtml;
               $itemBarCodeHtml = "<div class='barcode-container'>
                    <div class='barcode-title'>Bar Code</div>
                    <div class='barcode-content'><barcode type='EAN128A' code='{$product->getBarcode()}' text='1' class='' /></div>
                </div>";
               $orderItemsDetailHtml .= $itemBarCodeHtml;
           }
       }

        $delivery = $this->deliveryDateProvider->findByOrderId($orderData['order_id']);
        if ($delivery->getId()) {
            $orderNoteDetailHtml = "<div class='note-container'>
                <div class='note-title'>Notes:</div>
                <div class='note-content'>{$delivery->getData('comment')}</div>
            </div>";
        }

       $orderBillingDetailHtml = "
            <table>
               <tr>
                    <td>
                        Received as per order<br/>
                        Print Name: -----------<br/>
                        Signature:  -----------<br/>
                        Date:       -----------<br/>
                    </td>
                    <td>
                        Made By:    -----------<br/>
                        Serve By:   -----------<br/>
                        {$currencySymbol}{$orderData['grand_total']} Order<br/>
                        {$currencySymbol}{$orderData['grand_total']} Paid Online<br/>
                    </td>
                </tr>
            </table>
       ";
       $html = "
            <style>
            table { border-collapse: collapse; margin-top: 0; }
            td { padding: 0.5em; }
            h1 { margin-bottom: 0; }
            </style>
            <table>
                <tr>
                    <td>GIF</td>
                    <td>
                        <div>Order number: {$orderData['order_no']}</div>
                        <div>Date: {$orderData['delivery_date']}</div>
                        <div>Time: {$orderData['delivery_time']}</div>
                        <div>Billing Name: {$orderData['firstname']} {$orderData['lastname']}</div>
                        <div>Billing Tel: {$orderData['phone_no']}</div>
                        <div>Billing Email: {$orderData['email']}</div>
                    </td>
                </tr>
            </table>
            {$orderItemsDetailHtml}
            {$orderNoteDetailHtml}
            {$orderBillingDetailHtml}
            <br />
        ";
       $mpdf = new \Mpdf\Mpdf([
           'tempDir' =>  $this->directory->getPath('media') . '/tmp/mpdf',
           'margin_left' => 20,
           'margin_right' => 15,
           'margin_top' => 25,
           'margin_bottom' => 25,
           'margin_header' => 10,
           'margin_footer' => 10,
           'showBarcodeNumbers' => FALSE
       ]);
       try {
           $mpdf->WriteHTML($html);
       } catch (\Mpdf\MpdfException $e) {
           die($e->getMessage());
       }
       $mpdf->Output();
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
            'order_no'=>$orderobj->getData('increment_id'),
            'date_time'=>$orderobj->getData('created_at'),
            'firstname'=>$orderobj->getData('customer_firstname'),
            'lastname'=>$orderobj->getData('customer_lastname'),
            'email'=>$orderobj->getData('customer_email'),
            'phone_no'=>$orderobj->getBillingAddress()->getTelephone(),
            'delivery_date' => $this->timezone->formatDate($amastyOrderEntity->getDate(), \IntlDateFormatter::FULL, false),
            'delivery_time_from' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeFrom()),
            'delivery_time_to' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeTo()),
            'delivery_time' => $this->timeHandler->convertTime($amastyOrderEntity->getTimeFrom()) . ' - ' . $this->timeHandler->convertTime($amastyOrderEntity->getTimeTo()),
            'grand_total'=>$orderobj->getGrandTotal()
        ];
        return $orderDetails;
    }

    public function getOrderItemData($orderobj)
    {
        $orderItems=[];
        $orderItems = $orderobj->getAllVisibleItems();
        // echo count($orderItems);
        return $orderItems;
    }

    public function isJson($string,$return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }
}
