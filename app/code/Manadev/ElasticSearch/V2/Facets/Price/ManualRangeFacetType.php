<?php

namespace Manadev\ElasticSearch\V2\Facets\Price;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Price\ManualRangeFacet;
use Manadev\ProductCollection\Resources\Facets\Price\ManualRangeFacetResource;

/**
 * @method ManualRangeFacetResource getMysqlResource(Facet $facet)
 */
class ManualRangeFacetType extends FacetType
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

        if (!$range = $facet->getQuery()->getCategory()->getData('filter_price_range')) {
            $range = $this->getProductCollectionConfiguration()->getDefaultPriceNavigationStep();
        }

        /* @var $facet ManualRangeFacet */

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

        $maxNumberOfIntervals = $this->getProductCollectionConfiguration()->getMaxNumberOfPriceIntervals();
        if (count($result) > $maxNumberOfIntervals) {
            $extra = array_slice($result, $maxNumberOfIntervals);
            $result = array_slice($result, 0, $maxNumberOfIntervals, true);
            $index = array_keys($result)[count($result) - 1];
            foreach ($extra as $entry) {
                $result[$index]['count'] += $entry['count'];
            }

            // fix to override "20000-25000" value with "20000-" value,
            // and thus include the products from cut off price intervals
            unset($result[$index]['value']);

            $this->getLayeredHelperResource()->formatPriceRangeFacet($result[$index],
                $index * $range, ($index + 1) * $range,
                $index === $firstIndex , true);

            foreach ($facet->getAppliedRanges() as $appliedRange) {
                if ($result[$index]['value'] == "{$appliedRange[0]}-{$appliedRange[1]}") {
                    $result[$index]['is_selected'] = true;
                    break;
                }
            }
        }

        $facet->setData($this->addNonExistentAppliedPriceRanges($facet, array_values($result)));
    }

    protected function getIndex($value, $range) {
        return intval(floor($value / $range));
    }
}
