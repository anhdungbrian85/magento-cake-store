<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use Magento\Customer\Model\Session as CustomerSession;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemListInterface;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemListInterfaceFactory;
use WeltPixel\GA4\Api\ServerSide\Events\ViewItemListItemInterfaceFactory;
use WeltPixel\GA4\Helper\ServerSideTracking as GA4Helper;

class ViewItemListBuilder implements \WeltPixel\GA4\Api\ServerSide\Events\ViewItemListBuilderInterface
{
    /**
     * @var ViewItemListInterfaceFactory
     */
    protected $viewItemListFactory;

    /**
     * @var ViewItemListItemInterfaceFactory
     */
    protected $viewItemListItemFactory;

    /**
     * @var GA4Helper
     */
    protected $ga4Helper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;


    /**
     * @param ViewItemListInterfaceFactory $viewItemListFactory
     * @param ViewItemListItemInterfaceFactory $viewItemListItemFactory
     * @param GA4Helper $ga4Helper
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ViewItemListInterfaceFactory $viewItemListFactory,
        ViewItemListItemInterfaceFactory $viewItemListItemFactory,
        GA4Helper $ga4Helper,
        CustomerSession $customerSession
    )
    {
        $this->viewItemListFactory = $viewItemListFactory;
        $this->viewItemListItemFactory = $viewItemListItemFactory;
        $this->ga4Helper = $ga4Helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $paramsOptions
     * @return null|ViewItemListInterface
     */
    function getViewItemListEvent($paramsOptions)
    {
        /** @var ViewItemListInterface $viewItemListEvent */
        $viewItemListEvent = $this->viewItemListFactory->create();

        if (!$paramsOptions) {
            return $viewItemListEvent;
        }

        $clientId = $this->ga4Helper->getClientId();
        $sessionIdAndTimeStamp = $this->ga4Helper->getSessionIdAndTimeStamp();
        $userId = $this->customerSession->getCustomerId();

        if ($this->ga4Helper->sendUserIdInEvents() && $userId) {
            $viewItemListEvent->setUserId($userId);
        }
        $viewItemListEvent->setClientId($clientId);
        if ($sessionIdAndTimeStamp['session_id']) {
            $viewItemListEvent->setSessionId($sessionIdAndTimeStamp['session_id']);
        }
        if ($sessionIdAndTimeStamp['timestamp']) {
            $viewItemListEvent->setTimestamp($sessionIdAndTimeStamp['timestamp']);
        }

        $viewItemListEvent->setItemListId($paramsOptions['item_list_id']);
        $viewItemListEvent->setItemListName($paramsOptions['item_list_name']);

        foreach ($paramsOptions['items'] as $itemOptions) {
            $viewItemListItem = $this->viewItemListItemFactory->create();
            $viewItemListItem->setParams($itemOptions);

            $viewItemListEvent->addItem($viewItemListItem);
        }

        return $viewItemListEvent;
    }
}
