<?php

namespace Manadev\ElasticSearch\V2\Facets\Category;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\LayeredNavigationCategoryTree\Resources\Facets\Category\AbstractChildFacetResource;
use Manadev\ProductCollection\Contracts\Facet;

/**
 * @method AbstractChildFacetResource getMysqlResource(Facet $facet)
 */
class ParentChildrenFacetType extends FacetType
{
    public function getFacetCategoryId(Facet $facet) {
        return $this->getStore()->getRootCategoryId();
    }

    public function request(Facet $facet) {
        $this->requestTerm($facet, 'category_ids');
    }

    public function populate(Facet $facet) {
        if (!($counts = $this->getTermCounts($facet))) {
            return;
        }

        $this->getMysqlResource($facet)->setFacet($facet);
        $categories = $this->getMysqlResource($facet)->getCategoryData();
        $this->addCountToCategories($categories, $counts);
        $result = $this->getMysqlResource($facet)->toFilterData($categories);
        $result = $this->getMysqlResource($facet)->organizeChildren($result);

        if (count($result)) {
            $facet->setData($result);
        }
    }
}