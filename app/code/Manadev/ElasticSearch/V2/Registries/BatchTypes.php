<?php

namespace Manadev\ElasticSearch\V2\Registries;

use Manadev\Core\Exceptions\InterfaceNotImplemented;
use Manadev\ElasticSearch\V2\BatchType;

class BatchTypes
{
    /**
     * @var BatchType[]
     */
    protected $batchTypes;

    public function __construct(array $batchTypes)
    {
        foreach ($batchTypes as $type => $batchType) {
            if (!($batchType instanceof BatchType)) {
                throw new InterfaceNotImplemented(sprintf("'%s' does not implement '%s' interface.",
                    get_class($batchType),
                    BatchType::class));
            }
        }
        $this->batchTypes = $batchTypes;
    }

    /**
     * @param $name
     * @return bool|BatchType
     */
    public function get($name) {
        return isset($this->batchTypes[$name]) ? $this->batchTypes[$name] : false;
    }

    /**
     * @return bool|BatchType[]
     */
    public function getList() {
        return $this->batchTypes;
    }
}