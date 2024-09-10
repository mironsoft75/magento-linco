<?php

namespace Manadev\ElasticSearch\V2\Filters\LayeredFilters;

use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Filters\LayeredFilters\PriceFilter;

class PriceFilterType extends FilterType
{
    public function apply(Filter $filter) {
        /* @var PriceFilter $filter */
        $field = "{$filter->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $ranges = $this->getCurrencyHelper()->convertAppliedRanges(
            $filter->getRanges());

        if ($filter->getInclusive()) {
            $this->applyInclusiveRange($field, $ranges[0]);
        }
        else {
            $this->applyExclusiveRanges($field, $ranges);
        }
    }
}
