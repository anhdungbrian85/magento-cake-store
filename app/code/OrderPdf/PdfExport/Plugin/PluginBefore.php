<?php
namespace OrderPdf\PdfExport\Plugin;

use Magento\Sales\Block\Adminhtml\Order\Create;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;

class PluginBefore
{
     /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */

    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    )
    {
        if ($context->getRequest()->getFullActionName() == 'sales_order_view') {
            $order_id = $context->getRequest()->getParam('order_id');
            $url = $context->getUrl('orderspdf/create/index', ['order_id' => $order_id]);
            $buttonList->add(
                'Order Creation Pdf',
                ['label' => __('Order Creation Pdf'), 'onclick' => 'setLocation("' . $url . '")', 'class' => 'reset'],
                -1
            );
        }
    }
}
?>