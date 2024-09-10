<?php

namespace Manadev\ElasticSearch\V2\Facets\Price;

use Magento\Framework\Search\Dynamic\Algorithm;
use Manadev\ElasticSearch\Resources\FacetResource;
use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Price\EqualizedCountFacet;
use Manadev\ProductCollection\Registries\FacetResources;

class EqualizedCountFacetType extends FacetType
{
    public $preparationNeeded = true;

    /**
     * @var Algorithm
     */
    protected $algorithm;

    public function __construct(FacetResources $mysqlFacetResources, FacetResource $resource,
        Algorithm $algorithm)
    {
        parent::__construct($mysqlFacetResources, $resource);
        $this->algorithm = $algorithm;
    }

    public function prepare(Facet $facet) {
        $field = "{$facet->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $this->requestStats($facet, $field);
        $this->requestTerm($facet, $field);
    }

    public function request(Facet $facet) {
        $field = "{$facet->getName()}_{$this->getCustomerGroupId()}_{$this->getWebsiteId()}";
        $this->requestTerm($facet, $field);
    }

    public function populate(Facet $facet) {
        $stats = $this->getPreparedStats($facet);
        if (empty($stats['count'])) {
            return;
        }

        /* @var $facet EqualizedCountFacet */
        $rangeCount = $this->getRangeCount($stats);
        $countPerRange = $stats['count'] / $rangeCount;

        $result = [['from' => null, 'to' => null, 'count' => 0]];
        $current = 0;
        $count = 0;
        $total = 0;

        if (!($preparedTermCounts = $this->getPreparedTermCountsAsArray($facet))) {
            return;
        }

        foreach ($preparedTermCounts as $item) {
            if ($count > 0 && $total >= $countPerRange * ($current + 1)) {
                $result[$current]['to'] = $item['term'];
                $result[] = ['from' => $item['term'], 'to' => null, 'count' => 0];
                $count = 0;
                $current++;
            }

            $result[$current]['to'] = $item['term'];
            $count += $item['count'];
            $total += $item['count'];
        }

        if (!($termCounts = $this->getTermCounts($facet))) {
            return;
        }

        foreach ($termCounts as $term => $value) {
            foreach ($result as $index => &$item) {
                if ($item['from'] !== null && $term < $item['from']) {
                    continue;
                }

                if ($index < $rangeCount - 1 && $term >= $item['to']) {
                    continue;
                }

                $item['count'] += $value;
            }
        }

        foreach (array_keys($result) as $index) {
            if ($result[$index]['count'] === 0) {
                unset($result[$index]);
            }
        }
        $result = array_values($result);

        foreach ($result as $index => &$item) {
            $this->getLayeredHelperResource()->formatPriceRangeFacet($item,
                $item['from'], $item['to'], $item['from'] === null,
                $item['to'] === $result[count($result) - 1]['to']);

            $item['sort_order'] = $index;
            $item['is_selected'] = false;
            foreach ($facet->getAppliedRanges() as $appliedRange) {
                if ($item['value'] == "{$appliedRange[0]}-{$appliedRange[1]}") {
                    $item['is_selected'] = true;
                    break;
                }
            }
        }

        $facet->setData($this->addNonExistentAppliedPriceRanges($facet, $result));
    }

    protected function getRangeCount($stats) {
        $this->algorithm->setStatistics($stats['min'], $stats['max'], $stats['std_deviation'], $stats['count']);

        $class = new \ReflectionClass($this->algorithm);
        $method = $class->getMethod('getIntervalsNumber');
        $method->setAccessible(true);
        return $method->invoke($this->algorithm);
    }
}