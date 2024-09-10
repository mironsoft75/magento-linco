<?php

namespace Manadev\ElasticSearch\V2\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ElasticSearch\V2\FacetType;

class FacetTypes
{
    /**
     * @var FacetType[]
     */
    protected $facetTypes;

    public function __construct(array $facetTypes)
    {
        foreach ($facetTypes as $type => $facetType) {
            if (!($facetType instanceof FacetType)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($facetType),
                    FacetType::class));
            }
        }
        $this->facetTypes = $facetTypes;
    }

    /**
     * @param $name
     * @return bool|FacetType
     */
    public function get($name) {
        return isset($this->facetTypes[$name]) ? $this->facetTypes[$name] : false;
    }

    /**
     * @return bool|FacetType[]
     */
    public function getList() {
        return $this->facetTypes;
    }
}