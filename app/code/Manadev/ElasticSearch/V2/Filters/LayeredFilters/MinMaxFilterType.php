<?php

namespace Manadev\ElasticSearch\V2\Filters\LayeredFilters;

use Manadev\ElasticSearch\V2\FilterType;
use Manadev\LayeredNavigationSliders\Filters\MinMaxFilter;
use Manadev\ProductCollection\Contracts\Filter;

class MinMaxFilterType extends FilterType
{
    protected $_maxFilter;

    public function apply(Filter $filter) {
        /* @var MinMaxFilter $filter */
        $this->applyMinMaxRange($filter->getName(), $this->_getMaxFilter($filter)->getData('param_name'),
            $filter->getRanges()[0]);
    }

    /**
     * @param Filter $filter
     *
     * @return mixed
     */
    protected function _getMaxFilter(Filter $filter) {
        /* @var MinMaxFilter $filter */
        if (!$this->_maxFilter && $filter->getMaxAttributeId()) {
            foreach ($this->getLayeredHelper()->getAllFilters() as $layeredFilter) {
                if ($layeredFilter->getData('attribute_id') == $filter->getMaxAttributeId()) {
                    $this->_maxFilter = $layeredFilter;
                    break;
                }
            }
        }

        return $this->_maxFilter;
    }
}