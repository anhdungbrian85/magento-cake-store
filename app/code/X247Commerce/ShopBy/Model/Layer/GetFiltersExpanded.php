<?php

namespace X247Commerce\ShopBy\Model\Layer;

class GetFiltersExpanded extends \Amasty\Shopby\Model\Layer\GetFiltersExpanded
{
    /**
     * @return int[]
     */
    public function execute(?array $filters = null): array
    {
        $listExpandedFilters = [];
        $filters = $filters ?? $this->getFilters();
        $position = 0;

        foreach ($filters as $filter) {
            if (!$filter->getItemsCount()) {
                continue;
            }
            $listExpandedFilters[] = $position;
            $position++;
        }
        return $listExpandedFilters;
    }
}