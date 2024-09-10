<?php

namespace Manadev\ElasticSearch\V5;

use Manadev\ElasticSearch\V2\QueryEngine as V2QueryEngine;

class QueryEngine extends V2QueryEngine
{
    protected $fieldsClause = 'stored_fields';

    public function isEnabled() {
        return $this->features->isEnabled(__CLASS__);
    }
}