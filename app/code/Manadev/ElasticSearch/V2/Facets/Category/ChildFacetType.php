<?php

namespace Manadev\ElasticSearch\V2\Facets\Category;

use Magento\Catalog\Model\Category;
use Manadev\ElasticSearch\V2\FacetType;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Facets\Category\ChildFacet;

class ChildFacetType extends FacetType
{
    public function request(Facet $facet) {
        $this->requestTerm($facet, 'category_ids');
    }

    /**
     * @param ChildFacet|Facet $facet
     * @return string|null
     */
    public function populate(Facet $facet) {
        if (!($counts = $this->getTermCounts($facet))) {
            return;
        }

        $category = $facet->getAppliedCategory() ?: $facet->getQuery()->getCategory();
        if (!$category->getIsActive()) {
            return;
        }

        $result = [];
        foreach ($category->getChildrenCategories() as $childCategory) {
            /* @var $childCategory Category */
            if (!$childCategory->getIsActive()) {
                continue;
            }

            if (!isset($counts[$childCategory->getId()])) {
                continue;
            }

            if (!($count = $counts[$childCategory->getId()])) {
                continue;
            }

            $result[] = [
                'label' => $childCategory->getName(),
                'value' => $childCategory->getId(),
                'count' => $count,
                'is_selected' => 0,
                'sort_order' => count($result),
            ];
        }

        $minimumOptionCount = $facet->getHideWithSingleVisibleItem() ? 2 : 1;
        if (count($result) < $minimumOptionCount) {
            return;
        }

        $facet->setData($result);
    }
}