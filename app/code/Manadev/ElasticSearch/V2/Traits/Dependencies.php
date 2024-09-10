<?php

namespace Manadev\ElasticSearch\V2\Traits;

use Manadev\Core\Traits\FrontendDependencies;
use Manadev\ElasticSearch\Configuration;
use Manadev\ElasticSearch\CurrencyHelper;
use Manadev\ElasticSearch\V2\QueryEngine;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\QueryEngine as QueryEngineInterface;
use Manadev\ProductCollection\QueryRunner;

trait Dependencies
{
    use FrontendDependencies;

    /**
     * @return QueryRunner
     */
    protected function getQueryRunner() {
        return $this->getObjectManager()->get(QueryRunner::class);
    }

    /**
     * @return QueryEngine|QueryEngineInterface
     */
    protected function getEngine() {
        return $this->getQueryRunner()->getEngine();
    }

    /**
     * @return object
     */
    protected function getQuery() {
        return $this->getEngine()->currentQuery;
    }

    protected function getResponse(Facet $facet) {
        return $this->getEngine()->getResponse($facet);
    }

    protected function getPreparationResponse(Facet $facet) {
        return $this->getEngine()->getPreparationResponse($facet);
    }

    protected function getProductIds() {
        return $this->getEngine()->getProductIds();
    }

    /**
     * @return CurrencyHelper
     */
    protected function getCurrencyHelper() {
        return $this->getObjectManager()->get(CurrencyHelper::class);
    }

    /**
     * @return Configuration
     */
    protected function getElasticConfiguration() {
        return $this->getObjectManager()->get(Configuration::class);
    }

}
