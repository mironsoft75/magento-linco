<?php

namespace Manadev\ElasticSearch\V2\Facets\Decimal;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\LayeredNavigationSliders\Facets\Decimal\MinMaxSliderRangeFacet;
use Manadev\ProductCollection\Contracts\Facet;

class MinMaxSliderRangeFacetType extends FacetType
{
    protected $_maxFilter;

    public function getFilterCallback(Facet $facet) {
        return $this->getMysqlResource($facet)->getPreparationFilterCallback($facet);
    }

    public function request(Facet $facet) {
        $this->requestTerm($facet, $facet->getName());
        $this->requestStats($facet, $facet->getName());
    }

    public function populate(Facet $facet) {
        /* @var $facet MinMaxSliderRangeFacet */
        if ($facet->getMinMaxRole() == 'max') {
            return;
        }

        if (!($maxFilter = $this->_getMaxFilter($facet))) {
            return;
        }
        $maxFacet = $this->getEngine()->getFacet($maxFilter->getData('param_name'));

        $stats = $this->getStats($facet);
        $maxStats = $this->getStats($maxFacet);
        if ($stats['count'] <= 1 || $maxStats['count'] <= 1 || $maxStats['max'] <= $stats['min']) {
            return;
        }

        $terms = array_unique(array_merge($this->getTerms($facet), $this->getTerms($maxFacet)));
        sort($terms);

        $data = [
            'is_selected' => false,
            'sort_order' => 0
        ];

        if($appliedRanges = $facet->getAppliedRanges()) {
            $from = $appliedRanges[0][0];
            $to = $appliedRanges[0][1];
            $this->getLayeredHelperResource()->formatCustomRangeFacet($data, $from, $to, $facet->getNumberFormat(), $facet->isShowThousandSeparator());
            $data['is_selected'] = true;
        }

        $data = array_merge($data, [
            'min_range' => $stats['min'],
            'max_range' => $maxStats['max'],
            'available_values' => $terms,
        ]);

        $facet->setData($data);
    }

    /**
     * @param Facet $facet
     *
     * @return mixed
     */
    protected function _getMaxFilter(Facet $facet) {
        /** @var MinMaxSliderRangeFacet $facet */
        if (!$this->_maxFilter && $facet->getMaxFilterId()) {
            $filters = $this->getLayeredHelper()->getAllFilters();
            $this->_maxFilter = $filters->getItemById($facet->getMaxFilterId());
        }

        return $this->_maxFilter;
    }
}