<?php
namespace WeSupply\Toolbox\Plugin\Adminhtml\Order\View\Giftmessage;

use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Controller\Adminhtml\Order\View\Giftmessage\Save as GiftMessageSaveController;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;

class Save
{
    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;
    /**
     * @param ManagerInterface $eventManager
     * @param WeSupplyHelper $helper
     * @param OrderItemFactory $orderItemFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        WeSupplyHelper $helper,
        OrderItemFactory $orderItemFactory
    )
    {
        $this->eventManager = $eventManager;
        $this->helper = $helper;
        $this->orderItemFactory = $orderItemFactory;
    }
    /**
     * @param GiftMessageSaveController $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(
        GiftMessageSaveController $subject,
        \Closure $proceed
    ){
        $result = $proceed();

        if ($this->helper->getWeSupplyEnabled()) {

            $giftType = $subject->getRequest()->getParam('type');
            if ($giftType == 'order') {
                $orderId = $subject->getRequest()->getParam('entity');
            } else {
                $orderItemId = $subject->getRequest()->getParam('entity');
                $orderItem = $this->orderItemFactory->create()->load($orderItemId);
                $orderId = $orderItem->getOrderId();
            }

            if ($orderId) {
                $this->eventManager->dispatch(
                    'wesupply_order_update',
                    ['orderId' => $orderId]
                );
            }
        }


        return $result;
    }

}


