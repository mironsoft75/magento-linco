<?php

namespace Manadev\ElasticSearch\V2\Facets\Dropdown;

use Manadev\ElasticSearch\V2\FacetType;
use Manadev\LayeredNavigationSliders\Facets\Dropdown\DropdownSliderRangeFacet;
use Manadev\LayeredNavigationSliders\Resources\Facets\Dropdown\DropdownSliderRangeFacetResource;
use Manadev\ProductCollection\Contracts\Facet;

/**
 * @method DropdownSliderRangeFacetResource getMysqlResource(Facet $facet)
 */
class DropdownSliderRangeFacetType extends FacetType
{
    public function request(Facet $facet) {
        $this->requestTerm($facet);
    }

    public function populate(Facet $facet) {
        /* @var DropdownSliderRangeFacet $facet */
        if ($this->resource->usesCustomSourceModel($facet)) {
            return;
        }

        if (!($termCounts = $this->getTermCounts($facet))) {
            return;
        }

        $values = array_keys($termCounts);
        if (!($select = $this->resource->selectDropdownSlider($values))) {
            return;
        }
        $options = $this->resource->getConnection()->fetchAll($select);
        $this->getLayeredSorter()->sort($facet, $options);

        $availableValues = [];
        foreach ($options as $option) {
            $availableValues[$option['value']] = $option['label'];
        }

        $selectedIds = $facet->getSelectedOptionIds();
        $minRange = null;
        $maxRange = count($availableValues) - 1;
        $x = 0;
        if ($selectedIds) {
            foreach ($availableValues as $id => $label) {
                if (in_array($id, $selectedIds)) {
                    if (is_null($minRange)) {
                        $minRange = $x;
                    }
                    $maxRange = $x;
                }
                $x++;
            }
        }

        if (is_null($minRange)) {
            $minRange = 0;
        }

        $labels = array_values($availableValues);
        $data = [
            'min_applied_range' => $minRange,
            'max_applied_range' => $maxRange,
            'available_values' => $labels,
            'available_values_id' => array_keys($availableValues),
            'selected_ids' => $selectedIds,
            'is_selected' => (bool)$selectedIds,
            'sort_order' => 0,
        ];

        $this->getMysqlResource($facet)->formatItem($data, $labels[$minRange], $labels[$maxRange], $facet);
        $facet->setData($data);
    }
}