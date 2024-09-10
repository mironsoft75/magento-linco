<?php

namespace Manadev\ElasticSearch\V2;

use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ElasticSearch\Resources\FacetResource;
use Manadev\ElasticSearch\V2\Traits\Dependencies;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Price\EqualizedRangeFacet;
use Manadev\ProductCollection\Registries\FacetResources;

class FacetType
{
    use Dependencies;

    /**
     * @var FacetResources
     */
    protected $mysqlFacetResources;
    /**
     * @var FacetResource
     */
    protected $resource;

    public $preparationNeeded = false;

    public function __construct(FacetResources $mysqlFacetResources, FacetResource $resource) {
        $this->mysqlFacetResources = $mysqlFacetResources;
        $this->resource = $resource;
    }

    public function getFacetCategoryId(Facet $facet) {
        return null;
    }

    public function prepare(Facet $facet) {
        throw new NotImplemented();
    }

    public function request(Facet $facet) {
        throw new NotImplemented();
    }

    /**
     * @param Facet $facet
     * @return callback
     */
    public function getFilterCallback(Facet $facet) {
        return $this->getMysqlResource($facet)->getFilterCallback($facet);
    }

    /**
     * @param Facet $facet
     * @return string|null
     */
    public function populate(Facet $facet) {
        throw new NotImplemented();
    }

    public function getPreparationFilterCallback(Facet $facet) {
        return $this->getEngine()->getApplyNoFiltersCallback();
    }

    /**
     * @param Facet $facet
     */
    public function sort(Facet $facet) {
        $this->getMysqlResource($facet)->sort($facet);
    }

    protected function getMysqlResource(Facet $facet) {
        return $this->mysqlFacetResources->get($facet->getType());
    }

    protected function requestTerm(Facet $facet, $field = null) {
        if (!$field) {
            $field = $facet->getName();
        }

        if (isset($this->getQuery()->body['aggregations']["{$facet->getName()}_bucket"])) {
            return;
        }

        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            "aggregations" => [
                "{$facet->getName()}_bucket" => [
                    "terms" => [
                        "field" => $field,
                        "size" => 500,
                    ],
                ],
            ],
        ]);
    }

    protected function requestStats(Facet $facet, $field) {
        $this->getQuery()->body = array_merge_recursive($this->getQuery()->body, [
            "aggregations" => [
                "{$facet->getName()}_stats" => [
                    "extended_stats" => [
                        "field" => $field,
                    ],
                ],
            ],
        ]);
    }

    public function getTermCounts(Facet $facet) {
        $term = $this->getResponse($facet)['aggregations']["{$facet->getName()}_bucket"] ?? null;

        if (!$term || empty($term['buckets'])) {
            return null;
        }

        $result = [];

        foreach ($term['buckets'] as $bucket) {
            $result[(string)$bucket['key']] = $bucket['doc_count'];
        }

        return $result;
    }

    public function getTerms(Facet $facet) {
        $term = $this->getResponse($facet)['aggregations']["{$facet->getName()}_bucket"] ?? null;

        if (!$term || empty($term['buckets'])) {
            return null;
        }

        $result = [];

        foreach ($term['buckets'] as $bucket) {
            $result[] = $bucket['key'];
        }

        return $result;
    }


    public function getPreparedTermCountsAsArray(Facet $facet) {
        $term = $this->getPreparationResponse($facet)['aggregations']["{$facet->getName()}_bucket"] ?? null;

        if (!$term || empty($term['buckets'])) {
            return null;
        }

        $result = [];

        foreach ($term['buckets'] as $bucket) {
            $result[] = ['count' => $bucket['doc_count'], 'term' => $bucket['key']];
        }

        usort($result, function($a, $b) {
            if ($a['term'] < $b['term']) return -1;
            if ($a['term'] > $b['term']) return 1;
            return 0;
        });

        return $result;
    }

    protected function getStats(Facet $facet) {
        return $this->getResponse($facet)['aggregations']["{$facet->getName()}_stats"] ?? null;
    }

    protected function getPreparedStats(Facet $facet) {
        return $this->getPreparationResponse($facet)['aggregations']["{$facet->getName()}_stats"] ?? null;
    }

    protected function addCountToCategories(&$categories, $counts) {
        foreach ($categories as $key => &$category) {
            $category['product_count'] = $counts[$key] ?? 0;
        }
    }


    protected function addNonExistentAppliedPriceRanges(Facet $facet, $result) {
        return $this->addNonExistentAppliedRanges($facet, $result, 'formatPriceRangeFacet');
    }

    protected function addNonExistentAppliedDecimalRanges(Facet $facet, $result) {
        return $this->addNonExistentAppliedRanges($facet, $result, 'formatDecimalRangeFacet');
    }

    protected function addNonExistentAppliedRanges(Facet $facet, $result, $formatMethod) {
        $termCounts = $this->getTermCounts($facet);

        /* @var $facet EqualizedRangeFacet */
        $index = 0;

        $hiddenItems = [];

        foreach ($facet->getAppliedRanges() as $appliedRange) {
            $from = $appliedRange[0];
            $to = $appliedRange[1];
            if ($this->findRange($result, "{$from}-{$to}")) {
                continue;
            }

            $hasFrom = $from !== null && $from !== '';
            $hasTo = $to !== null && $to !== '';
            $item = [
                'count' => 0,
                'sort_order' => --$index,
                'is_selected' => true,
                'hidden' => true,
            ];

            if (!$termCounts) {
                continue;
            }

            foreach ($termCounts as $term => $value) {
                if ($hasFrom && $term < $from) {
                    continue;
                }

                if ($hasTo && $term >= $to) {
                    continue;
                }

                $item['count'] += $value;
            }

            if (!$item['count']) {
                continue;
            }

            if (!$hasFrom) {
                $stats = $this->getPreparedStats($facet);
                $this->getLayeredHelperResource()->$formatMethod($result[0],
                    $stats['min'], $result[0]['to'], false, false);
            }

            if (!$hasTo) {
                $stats = $this->getPreparedStats($facet);
                $this->getLayeredHelperResource()->$formatMethod($result[count($result) - 1],
                    $result[count($result) - 1]['from'], $stats['max'], false, false);
            }

            $this->getLayeredHelperResource()->$formatMethod($item, $from, $to, !$hasFrom, !$hasTo);

            $hiddenItems[] = $item;
        }

        return array_merge($hiddenItems, $result);
    }

    protected function findRange($result, $range) {
        foreach ($result as $entry) {
            if ($entry['value'] == $range) {
                return true;
            }
        }

        return false;
    }
}
