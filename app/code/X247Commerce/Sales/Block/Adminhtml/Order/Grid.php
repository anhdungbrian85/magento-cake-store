<?php

namespace X247Commerce\Sales\Block\Adminhtml\Order;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use X247Commerce\Sales\Helper\Data;
use X247Commerce\StoreLocatorSource\Helper\User;

class Grid extends Template
{

    protected Data $helper;
    protected User $userHelper;

    public function __construct(
        Template\Context $context,
        Data $helper,
        User $userHelper,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null,

    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->helper = $helper;
        $this->userHelper = $userHelper;
    }


    /**
     * @return bool
     */
    public function isStaffUser()
    {
        return $this->userHelper->isStaffUser();
    }

    /**
     * @return false|string
     */
    public function getEnableSortingColumns()
    {
        $columnsConfig = $this->helper->getEnableSortingColumns();
        $columnsConfig =  preg_split('/\r\n|\r|\n/', $columnsConfig);

        return json_encode($columnsConfig);
    }

}
