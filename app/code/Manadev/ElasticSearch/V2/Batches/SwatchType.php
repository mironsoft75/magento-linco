<?php

namespace Manadev\ElasticSearch\V2\Batches;

use Manadev\ElasticSearch\V2\BatchType;
use Manadev\ProductCollection\Contracts\FacetBatch;

class SwatchType extends BatchType
{
    public function populate(FacetBatch $batch) {
        $counts = $this->getTermCounts($batch);
        $optionIds = $this->getOptionIds($counts);
        $selectedOptionIds = $this->getSelectedOptionIds($batch);

        if (!($select = $this->resource->selectSwatchBatch($optionIds, $selectedOptionIds))) {
            return;
        }

        $this->populateFromSelect($batch, $select, $counts);
        $this->unpopulateEmptyFacets($batch);
        $this->addSelectedOptionsWhichAreNotInList($batch);
    }

}