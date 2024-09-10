<?php

namespace Manadev\ElasticSearch\V2\Facets\Decimal;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\LayeredNavigationSliders\Facets\Decimal\AbstractSliderFacet;
use Manadev\ProductCollection\Contracts\Facet;

class SliderRangeFacetType extends FacetType
{
    public function getFilterCallback(Facet $facet) {
        return $this->getMysqlResource($facet)->getPreparationFilterCallback($facet);
    }

    public function request(Facet $facet) {
        $this->requestTerm($facet, $facet->getName());
        $this->requestStats($facet, $facet->getName());
    }

    public function populate(Facet $facet) {
        /* @var $facet AbstractSliderFacet */
        if (!($counts = $this->getTermCounts($facet))) {
            return;
        }

        ksort($counts);
        $stats = $this->getStats($facet);

        if ($stats['count'] <= 1 || $stats['max'] <= $stats['min']) {
            return;
        }

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
            'max_range' => $stats['max'],
            'available_values' => array_keys($counts),
        ]);

        $facet->setData($data);
    }
}