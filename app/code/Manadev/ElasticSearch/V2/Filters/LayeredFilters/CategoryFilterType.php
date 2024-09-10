<?php

namespace Manadev\ElasticSearch\V2\Filters\LayeredFilters;


use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Filters\LayeredFilters\CategoryFilter;

class CategoryFilterType extends FilterType
{
    public function apply(Filter $filter) {
        /* @var CategoryFilter $filter */
        $this->getQuery()->body['query']['bool']['filter'][0]['term']['category_ids'] =
            (string)$filter->getIds()[0];
    }
}