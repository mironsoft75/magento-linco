<?php

namespace Manadev\ElasticSearch\V2\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ElasticSearch\V2\FilterType;
use Manadev\ProductCollection\Contracts\SupportedFilters;

class FilterTypes implements SupportedFilters
{
    /**
     * @var FilterType[]
     */
    protected $filterTypes;

    public function __construct(array $filterTypes)
    {
        foreach ($filterTypes as $filterType) {
            if (!($filterType instanceof FilterType)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($filterType),
                    FilterType::class));
            }
        }
        $this->filterTypes = $filterTypes;
    }

    /**
     * @param $name
     * @return bool|FilterType
     */
    public function get($name) {
        return isset($this->filterTypes[$name]) ? $this->filterTypes[$name] : false;
    }

    /**
     * @return bool|FilterType[]
     */
    public function getList() {
        return $this->filterTypes;
    }
}