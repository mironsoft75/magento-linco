<?php

namespace Manadev\ElasticSearch\V2\Facets\Price;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Price\EqualizedRangeFacet;
use Manadev\ProductCollection\Resources\Facets\Price\EqualizedRangeFacetResource;

/**
 * @method EqualizedRangeFacetResource getMysqlResource(Facet $facet)
 */
class EqualizedRangeFacetType extends FacetType
{
    public $preparationNeeded = true;

    public function prepare(Facet $facet) {
        $field = "{$facet->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $this->requestStats($facet, $field);
    }

    public function request(Facet $facet) {
        $field = "{$facet->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $this->requestTerm($facet, $field);
    }

    public function populate(Facet $facet) {
        $stats = $this->getPreparedStats($facet);
        $stats = $this->getCurrencyHelper()->convertStats($stats);
        if (empty($stats['count'])) {
            return;
        }

        $range = 1;
        while ($range * 10 < $stats['max'] - $stats['min']) {
            $range *= 10;
        }

        /* @var $facet EqualizedRangeFacet */

        $result = [];
        $firstIndex = $this->getIndex($stats['min'], $range);
        $lastIndex = $this->getIndex($stats['max'], $range);

        if (!($termCounts = $this->getTermCounts($facet))) {
            return;
        }

        $termCounts = $this->getCurrencyHelper()->convertCounts($termCounts);
        foreach ($termCounts as $value => $count) {
            $index = $this->getIndex($value, $range);

            if (!isset($result[$index])) {
                $result[$index] = [
                    'count' => 0,
                    'sort_order' => $index,
                    'is_selected' => false,
                ];

                $this->getLayeredHelperResource()->formatPriceRangeFacet($result[$index],
                    $index * $range, ($index + 1) * $range,
                    $index === $firstIndex , $index === $lastIndex);

                foreach ($facet->getAppliedRanges() as $appliedRange) {
                    if ($result[$index]['value'] == "{$appliedRange[0]}-{$appliedRange[1]}") {
                        $result[$index]['is_selected'] = true;
                        break;
                    }
                }
            }

            $result[$index]['count'] += $count;
        }

        ksort($result);
        $facet->setData($this->addNonExistentAppliedPriceRanges($facet, array_values($result)));
    }

    protected function getIndex($value, $range) {
        $result = floor($value / $range);
        return $result == 10 ? 9 : intval($result);
    }
}
