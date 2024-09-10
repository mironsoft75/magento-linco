<?php

namespace Manadev\ElasticSearch\V2\Filters;

use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Enums\Operation;
use Manadev\ProductCollection\Filters\LogicalFilter;

class LogicalFilterType extends FilterType
{
    /**
     * @param LogicalFilter|Filter $filter
     */
    public function apply(Filter $filter) {
        if ($filter->getOperator() != Operation::LOGICAL_AND) {
            throw new NotImplemented();
        }

        foreach ($filter->getOperands() as $operand) {
            $this->getEngine()->applyFilterRecursively($operand);
        }
    }
}