<?php

namespace Manadev\ElasticSearch\V2\Filters\LayeredFilters;

use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Filters\LayeredFilters\DropdownFilter;

class DropdownFilterType extends FilterType
{
    /**
     * @param Filter|DropdownFilter $filter
     */
    public function apply(Filter $filter) {
        switch ($filter->getOperation()) {
            case Operation::LOGICAL_OR:
                $this->applyTerms($filter->getName(), $filter->getOptionIds());
                break;
            case Operation::LOGICAL_AND:
                $this->applyAndTerms($filter->getName(), $filter->getOptionIds());
                break;
            default:
                throw new NotImplemented();
        }
    }
}