<?php

namespace Manadev\ElasticSearch\V2\Facets\Decimal;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Decimal\EqualizedRangeFacet;

class EqualizedRangeFacetType extends FacetType
{
    public $preparationNeeded = true;

    public function prepare(Facet $facet) {
        $this->requestStats($facet, $facet->getName());
    }

    public function request(Facet $facet) {
        $this->requestTerm($facet, $facet->getName());
    }

    public function populate(Facet $facet) {
        $stats = $this->getPreparedStats($facet);
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

        foreach ($termCounts as $value => $count) {
            $index = $this->getIndex($value, $range);

            if (!isset($result[$index])) {
                $result[$index] = [
                    'count' => 0,
                    'sort_order' => $index,
                    'is_selected' => false,
                ];

                $this->getLayeredHelperResource()->formatDecimalRangeFacet($result[$index],
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
        $facet->setData($this->addNonExistentAppliedDecimalRanges($facet, array_values($result)));
    }

    protected function getIndex($value, $range) {
        $result = floor($value / $range);
        return $result == 10 ? 9 : intval($result);
    }
}