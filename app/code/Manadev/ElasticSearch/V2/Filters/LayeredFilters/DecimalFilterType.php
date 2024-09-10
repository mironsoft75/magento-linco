<?php

namespace Manadev\ElasticSearch\V2\Filters\LayeredFilters;

use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Filters\LayeredFilters\DecimalFilter;

class DecimalFilterType extends FilterType
{
    public function apply(Filter $filter) {
        /* @var DecimalFilter $filter */
        $field = $filter->getName();
        if ($filter->getIsToRangeInclusive()) {
            $this->applyInclusiveRange($field, $filter->getRanges()[0]);
        }
        else {
            $this->applyExclusiveRanges($field, $filter->getRanges());
        }
    }
}