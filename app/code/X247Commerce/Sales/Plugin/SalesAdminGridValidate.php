<?php 

namespace X247Commerce\Sales\Plugin;

use X247Commerce\StoreLocatorSource\Helper\User as UserHelper;
use Magento\Backend\Model\Auth\Session;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class SalesAdminGridValidate
{
    protected $_adminSession;
    protected $locatorSourceResolver;
    protected UserHelper $userHelper;

    public function __construct(
        Session $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        UserHelper $userHelper
    ) {
        $this->_adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
    }
    public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $result,
        $requestName
    )
    {
        $user = $this->_adminSession->getUser();
        $isStaffUser = $this->userHelper->isStaffUser($user);

        $amLocatorStoresByUser = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);
        
        if ($isStaffUser) {
            if ($requestName == 'sales_order_grid_data_source') {
                
                $result->getSelect()
                        ->joinleft(['so' => 'sales_order'], 'main_table.entity_id=so.entity_id', [])
                        ->where('so.store_location_id IN (?)', $amLocatorStoresByUser);            
                return $result;
                
            }
            if ($requestName == 'sales_order_invoice_grid_data_source' || $requestName == 'sales_order_shipment_grid_data_source' || $requestName == 'sales_order_creditmemo_grid_data_source')
            {
                $result->getSelect()
                        ->joinleft(['so' => 'sales_order'], 'main_table.order_id=so.entity_id', [])
                        ->where('so.store_location_id IN (?)', $amLocatorStoresByUser);
                return $result;
            }
        }
        return $result;
    }
}