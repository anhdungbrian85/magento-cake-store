<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ulmod\Productinquiry\Model\ResourceModel\Data\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Ulmod_Productinquiry::delete';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        
        $collectionSize = $collection->getSize();
        foreach ($collection as $block) {
            $block->delete();
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been deleted.', $collectionSize)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        
        return $resultRedirect->setPath(
            '*/*/'
        );
    }
}
