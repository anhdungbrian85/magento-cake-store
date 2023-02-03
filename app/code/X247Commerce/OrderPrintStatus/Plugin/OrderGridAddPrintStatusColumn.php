<?php 

namespace X247Commerce\OrderPrintStatus\Plugin;

class OrderGridAddPrintStatusColumn
{
    public function afterGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        $result,
        $requestName
    )   {
        
        if ($requestName == 'sales_order_grid_data_source') {
            $result->getSelect()
                    ->joinleft(['opsso' => 'sales_order'], 'main_table.entity_id=opsso.entity_id', ['print_status']);
            return $result;
        }
        
        return $result;
    }
}