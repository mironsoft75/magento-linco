<?php

namespace Manadev\ElasticSearch\V2;

use Magento\Framework\DB\Select;
use Manadev\Core\Exceptions\NotImplemented;
use Manadev\ElasticSearch\Resources\FacetResource;
use Manadev\ElasticSearch\V2\Traits\Dependencies;
use Manadev\ProductCollection\Contracts\FacetBatch;
use Manadev\ProductCollection\Facets\Dropdown\OptimizedFacet;

class BatchType
{
    use Dependencies;

    /**
     * @var FacetResource
     */
    protected $resource;

    public function __construct(FacetResource $resource) {
        $this->resource = $resource;
    }

    public function populate(FacetBatch $batch) {
        throw new NotImplemented();
    }

    protected function getTermCounts(FacetBatch $batch) {
        $result = [];

        foreach ($batch->getFacets() as $key => $facet) {
            if ($counts = $this->getEngine()->getFacetType($facet)->getTermCounts($facet)) {
                $result[$key] = $counts;
            }
        }

        return $result;
    }

    protected function getOptionIds($counts) {
        $result = [];

        foreach ($counts as $facetCounts) {
            $result = array_merge($result, array_keys($facetCounts));
        }

        return $result;
    }


    protected function getSelectedOptionIds(FacetBatch $batch) {
        $result = [];

        foreach ($batch->getFacets() as $key => $facet) {
            /* @var OptimizedFacet $facet */
            if (($selected = $facet->getSelectedOptionIds()) !== false) {
                $result = array_merge($result, $selected);
            }
        }

        return $result;
    }

    protected function populateFromSelect(FacetBatch $batch, Select $select, $counts) {
        $db = $this->resource->getConnection();

        foreach ($this->getDbHelper()->fetchAllPaged($db, $select) as $record) {
            if (!isset($counts[$record['attribute_id']][$record['value']])) {
                continue;
            }

            $record['count'] = $counts[$record['attribute_id']][$record['value']];
            $batch->getFacet($record)->addRecord($record);
        }
    }

    protected function unpopulateEmptyFacets(FacetBatch $batch) {
        foreach ($batch->getFacets() as $individualFacet) {
            /* @var OptimizedFacet $individualFacet */
            $minimumOptionCount = $individualFacet->getHideWithSingleVisibleItem() ? 2 : 1;
            if (!($data = $individualFacet->getData())) {
                continue;
            }

            if (count($individualFacet->getData()) >= $minimumOptionCount) {
                continue;
            }

            $individualFacet->setData(false);
        }
    }

    protected function addSelectedOptionsWhichAreNotInList(FacetBatch $batch) {
        foreach ($batch->getFacets() as $individualFacet) {
            $individualFacet->setSelectedData($this->resource->getSelectedData($individualFacet));
        }
    }
}
