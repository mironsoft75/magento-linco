<?php

namespace Lyracons\Core\Api\Setup;


interface StoreBuilderServiceInterface
{
    /**
     * @param array $data
     * @return StoreBuilderServiceInterface
     */
    public function create(array $data);

}
