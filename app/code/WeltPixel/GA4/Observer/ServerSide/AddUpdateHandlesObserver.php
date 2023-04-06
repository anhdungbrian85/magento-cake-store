<?php
namespace WeltPixel\GA4\Observer\ServerSide;

use Magento\Framework\Event\ObserverInterface;

class AddUpdateHandlesObserver implements ObserverInterface
{
    /**
     * @var \WeltPixel\GA4\Helper\ServerSideTracking
     */
    protected $ga4Helper;

    /**
     * @param \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
     */
    public function __construct(
        \WeltPixel\GA4\Helper\ServerSideTracking $ga4Helper
    )
    {
        $this->ga4Helper = $ga4Helper;
    }

    /**
     * Add Custom layout handle
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getData('layout');

        /** Apply only on pages where page is rendered */
        $currentHandles = $layout->getUpdate()->getHandles();
        if (!in_array('default', $currentHandles)) {
            return $this;
        }

        if (!$this->ga4Helper->isServerSideTrakingEnabled() || !$this->ga4Helper->shouldEventBeTracked(\WeltPixel\GA4\Model\Config\Source\ServerSide\TrackingEvents::EVENT_SELECT_ITEM)) {
            return $this;
        }

        $layout->getUpdate()->addHandle('weltpixel_ga4_serverside_select_item');

        return $this;
    }
}
