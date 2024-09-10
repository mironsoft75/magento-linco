<?php

namespace Manadev\ElasticSearch\V2\Facets\Price;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\LayeredNavigationSliders\Facets\Decimal\AbstractSliderFacet;
use Manadev\ProductCollection\Contracts\Facet;

class PriceSliderRangeFacetType extends FacetType
{
    public function getFilterCallback(Facet $facet) {
        return $this->getMysqlResource($facet)->getPreparationFilterCallback($facet);
    }

    public function request(Facet $facet) {
        $field = "{$facet->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $this->requestTerm($facet, $field);
        $this->requestStats($facet, $field);
    }

    public function populate(Facet $facet) {
        /* @var $facet AbstractSliderFacet */
        if (!($counts = $this->getTermCounts($facet))) {
            return;
        }

        $counts = $this->getCurrencyHelper()->convertCounts($counts);

        ksort($counts);
        $stats = $this->getCurrencyHelper()->convertStats($this->getStats($facet));

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
