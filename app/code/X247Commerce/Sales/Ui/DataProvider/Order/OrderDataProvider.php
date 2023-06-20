<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace X247Commerce\Sales\Ui\DataProvider\Order;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;

/**
 * Class ProductDataProvider
 *
 * @api
 * @since 100.0.2
 */
class OrderDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $meta;

    protected $objectManager;

    protected $orderCollectionFactory;

    protected $userRole;

    protected $adminSession;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderCollectionFactory,
        \Magento\Backend\Model\Auth\Session $adminSession,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->objectManager = $objectManager;
        $this->adminSession = $adminSession;
    }

    public function getMeta()
    {   
        $this->setMeta();
        return $this->meta;
    }

    public function setMeta(){
        if($this->isStaffUser()){
            $this->disableAllColumns();
            $this->enableStaffCanDisplay();
        }
    }

    protected function enableStaffCanDisplay(){
        $columsCanView = $this->getStaffDisplayCols();
        foreach($this->meta['sales_order_columns']['children'] as $name => $config){
            if(in_array($name, $columsCanView)){
                unset($this->meta['sales_order_columns']['children'][$name]);
            }
        }
        return $this->meta;
    }

    protected function disableAllColumns(){
        $columns = $this->getColumns();
        foreach($columns as $column)
        {
            $this->meta['sales_order_columns']['children'][$column]['arguments']['data']['config']['componentDisabled'] = true;
        }
        return $this->meta;
    }

    // protected function getColumns()
    // {
    //     $orderCollection = $this->orderCollectionFactory->create();
    //     $fieldNames = $orderCollection->getConnection()->describeTable($orderCollection->getMainTable());
    //     return array_keys($fieldNames);
    // }

    protected function getColumns()
    {

        return [
            "ids",
            "increment_id",
            "store_id",
            "created_at",
            "billing_name",
            "shipping_name",
            "base_grand_total",
            "grand_total",
            "status",
            "billing_address",
            "shipping_address",
            "shipping_information",
            "customer_email",
            "customer_group",
            "subtotal",
            "shipping_and_handling",
            "customer_name",
            "payment_method",
            "total_refunded",
            "actions",
            "refunded_to_store_credit",
            "allocated_sources",
            "pickup_location_code",
            "transaction_source",
            "amasty_sociallogin_code",
            "print_status"
        ];
    }

    protected function getStaffDisplayCols(){
        return [
            "increment_id",
            "print_status",
            "customer_name",
            "subtotal",
            "created_at",
            "status",
            "base_grand_total",
            "status",
            "customer_name",
            "payment_method",
            "actions",
        ];

    }

    protected function getUserRole()
    {
        return $this->adminSession->getUser()->getRole();
    }

    protected function isStaffUser(){
        $userRole = $this->getUserRole();
        if($userRole->getRoleName() == 'Staff'){
            return true;
        }
        return false;
    }

}
