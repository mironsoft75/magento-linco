<?php

namespace Manadev\ElasticSearch\V2\Facets\Swatch;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Resources\Facets\Swatch\StandardFacetResource;

/**
 * @method StandardFacetResource getMysqlResource(Facet $facet)
 */
class StandardFacetType extends FacetType
{
    public function request(Facet $facet) {
        $this->requestTerm($facet);
    }

    public function populate(Facet $facet) {
        if ($this->resource->usesCustomSourceModel($facet)) {
            return null;
        }

        if (!($counts = $this->getTermCounts($facet))) {
            return;
        }

        $facet->setData($this->getMysqlResource($facet)->populateOptions($facet, $counts));
    }
}