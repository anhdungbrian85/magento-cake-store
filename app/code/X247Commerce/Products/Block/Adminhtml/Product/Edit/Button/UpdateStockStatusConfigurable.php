<?php

namespace X247Commerce\Products\Block\Adminhtml\Product\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class UpdateStockStatusConfigurable extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    const IN_STOCK = 1;
    
    const OUT_OF_STOCK = 0;

    protected $request;

    protected $adminSession;

    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Backend\Model\Auth\Session $adminSession,
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->request = $request;
        $this->adminSession = $adminSession;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $roleId = (int) $roleData['role_id'];
        if ($roleId == 1 || $this->request->getParam('id') == NULL) {
            return [];
        }
        return [
            'label' => __('Set Child Products Out of Stock'),
            'class' => 'action-secondary',
            'on_click' => 'updateStockStatus(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->urlBuilder->getUrl('x247product/product/updateStockStatus',
                    [
                        'product_id' => $this->request->getParam('id'),
                        'status' => self::OUT_OF_STOCK
                    ]
                ) . '\', {data: {}})',
            'sort_order' => 20
        ];
    }
}
