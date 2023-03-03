<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ulmod\Productinquiry\Model\ConfigData
     */
    protected $configData;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ulmod\Productinquiry\Model\ConfigData $configData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ulmod\Productinquiry\Model\ConfigData $configData
    ) {
    
        $this->configData = $configData;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create(false, ['isIsolated' => true]);
        $layout = $this->configData->getListLayout();
        $pageConfig = $resultPage->getConfig();
        $pageConfig->setPageLayout($layout);

        return $resultPage;
    }
}
