<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemBuilderInterface
{
    /**
     * @param $productId
     * @return null|ViewItemInterface
     */
    function getViewItemEvent($productId);
}
