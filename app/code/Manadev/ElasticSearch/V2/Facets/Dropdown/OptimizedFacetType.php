<?php

namespace Manadev\ElasticSearch\V2\Facets\Dropdown;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;

class OptimizedFacetType extends FacetType
{
    public function request(Facet $facet) {
        $this->requestTerm($facet);
    }

    public function populate(Facet $facet) {
        if ($this->resource->usesCustomSourceModel($facet)) {
            return null;
        }

        return 'dropdown';
    }
}