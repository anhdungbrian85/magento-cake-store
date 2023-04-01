<?php

declare(strict_types=1);

namespace OrderPdf\PdfExport\Plugin\Button;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;

class ToolbarPlugin
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * ToolbarPlugin constructor.
     * @param AuthorizationInterface $authorization
     * @param ConfigInterface $config
     * @param Escaper $escaper
     * @param DataProvider $dataProvider
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ConfigInterface $config,
        Escaper $escaper,
        DataProvider $dataProvider
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->escaper = $escaper;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Add Login as Customer button.
     *
     * @param ToolbarInterface $subject
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePushButtons(
        ToolbarInterface $subject,
        AbstractBlock $context,
        ButtonList $buttonList
    ): void {
        $nameInLayout = $context->getNameInLayout();

        $order = $this->getOrder($nameInLayout, $context);
        if ($order) {
            $url = $context->getUrl('orderspdf/create/index', ['order_id' => $order->getId()]);
            $buttonList->add(
                'print_order_pdf',
                ['label' => __('Print Order Pdf'), 'onclick' => 'setLocation("' . $url . '")', 'class' => 'reset'],
                -1
            );
        }
    }

    /**
     * Extract order data from context.
     *
     * @param string $nameInLayout
     * @param AbstractBlock $context
     * @return array|null
     */
    private function getOrder(string $nameInLayout, AbstractBlock $context)
    {
        switch ($nameInLayout) {
            case 'sales_order_edit':
                return $context->getOrder();
        }

        return null;
    }
}
