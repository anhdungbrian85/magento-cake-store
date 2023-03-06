<?php

namespace OrderPdf\PdfExport\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Amasty\StorePickupWithLocator\Model\TimeHandler;

class Data extends AbstractHelper
{
    protected $catalogImageHelper;

    protected $productRepository;

    protected $orderRepository;

    protected $timezone;

    protected $timeHandler;

    protected $catalogProductTypeConfigurable;

    public function __construct(
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
    }

    public function createOrderPdf($order,$_fileFactory)
    {
       $orderData = $this->getOrderData($order);
       $itemsData = $this->getOrderItemData($order);
       if (empty($orderData)) {
           return;
       }
       $orderItemsDetailHtml = '';
       if(isset($itemsData) && $itemsData!=null) {
           foreach ($itemsData as $item) {
                $product = $this->productRepository->get($item->getSku());
                $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
                $sku = $item->getSku();
                if (isset($parentByChild[0])) {
                    $sku = $this->productRepository->getById($parentByChild[0])->getSku();
                }
                $shape = $item->getProduct()->getAttributeText('shape') ? $item->getProduct()->getAttributeText('shape'):" ";
                $sponge = $product->getAttributeText('sponge') ? $product->getAttributeText('sponge'):" ";
                $size_serving = $product->getAttributeText('size_servings') ? $product->getAttributeText('size_servings'):" ";
                $base  = substr($sponge, 0, 1);//position,count V
                $size = str_replace('"'," ",substr($size_serving, 0, 3)); // 10 6
                $imageUrl = $this->catalogImageHelper->init($product, 'product_page_image_small')
                                ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
                                ->resize(380)
                                ->getUrl();
                $itemHtml = "
                        <table>
                            <tr>
                            <td>Ref</td>
                            <td>Image</td>
                            <td>Base</td>
                            <td>Shape</td>
                            <td>Size</td>
                            <td>Bar Code</td>
                        </tr>
                            <tr>
                            <td>{$sku}</td>
                            <td><img style='vertical-align: top' src='{$imageUrl}?t=jpg' width='80' /></td>
                            <td>{$base}</td>
                            <td>{$shape}</td>
                            <td>{$size}</td>
                            <td><barcode code='{$product->getBarcode()}' text='1' class='' /></td>
                        </tr>
                    </table>";
                $orderItemsDetailHtml .= $itemHtml;
           }
       }
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
            <br />
        ";

       $mpdf = new \Mpdf\Mpdf([
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

    public function getOrderData($orderobj)
    {
        $amastyOrderEntity = $this->orderRepository->getByOrderId($orderobj->getId());
        $orderDetails = [
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

    public function is_json($string,$return_data = false) {
        $data = json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE) ? ($return_data ? $data : TRUE) : FALSE;
    }
}
