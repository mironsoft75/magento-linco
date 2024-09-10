<?php

namespace Manadev\ElasticSearch\V2;

use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Manadev\Core\Features;
use Manadev\Core\Resources\TemporaryResource;
use Manadev\ElasticSearch\V2\Registries\BatchTypes;
use Manadev\ElasticSearch\V2\Registries\FacetTypes;
use Manadev\ElasticSearch\V2\Registries\FilterTypes;
use Manadev\ProductCollection\Contracts\Facet;
use Manadev\ProductCollection\Contracts\FacetBatch;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\SupportedFilters;
use Manadev\ProductCollection\Contracts\ProductCollection;
use Manadev\ProductCollection\Contracts\QueryEngine as BaseQueryEngine;
use Manadev\ProductCollection\Factory;
use Manadev\ProductCollection\Resources\Collections\FullTextProductCollection;
use Manadev\ProductCollection\Resources\QueryEngineResource;

class QueryEngine implements BaseQueryEngine
{
    protected $fieldsClause = 'fields';

    #region Temporary properties
    /**
     * @var FullTextProductCollection
     */
    protected $collection;
    /**
     * @var object[]
     */
    protected $queries;
    /**
     * @var string
     */
    protected $mainKey;
    /**
     * @var string[]
     */
    protected $facetQueryKeys;
    /**
     * @var string[]
     */
    protected $facetPreparationQueryKeys;
    /**
     * @var array
     */
    protected $responses;
    /**
     * @var int[]
     */
    protected $productIds;
    /**
     * @var array
     */
    protected $productIdsAndScores;
    /**
     * @var FacetBatch[]
     */
    protected $batches;
    /**
     * @var callable
     */
    protected $callback;
    /**
     * @var object
     */
    public $currentQuery;
    #endregion

    /**
     * @var FilterTypes
     */
    protected $filterTypes;
    /**
     * @var FacetTypes
     */
    protected $facetTypes;
    /**
     * @var Config
     */
    protected $elasticConfig;
    /**
     * @var QueryEngineResource
     */
    protected $mysqlQueryEngine;
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var BatchTypes
     */
    protected $batchTypes;
    /**
     * @var TemporaryResource
     */
    protected $temporaryResource;
    /**
     * @var Features
     */
    protected $features;

    public function __construct(
        FilterTypes $filterTypes,
        FacetTypes $facetTypes,
        BatchTypes $batchTypes,
        Config $elasticConfig,
        QueryEngineResource $mysqlQueryEngine,
        ConnectionManager $connectionManager,
        Factory $factory,
        TemporaryResource $temporaryResource,
        Features $features
    ) {
        $this->filterTypes = $filterTypes;
        $this->facetTypes = $facetTypes;
        $this->elasticConfig = $elasticConfig;
        $this->mysqlQueryEngine = $mysqlQueryEngine;
        $this->connectionManager = $connectionManager;
        $this->factory = $factory;
        $this->batchTypes = $batchTypes;
        $this->temporaryResource = $temporaryResource;
        $this->features = $features;
    }

    /**
     * @param Facet $facet
     * @return FacetType
     */
    public function getFacetType(Facet $facet) {
        return $this->facetTypes->get($facet->getType());
    }

    /**
     * @param Filter $filter
     * @return FilterType
     */
    public function getFilterType(Filter $filter) {
        return $this->filterTypes->get($filter->getType());
    }

    /**
     * @param FacetBatch $batch
     * @return BatchType
     */
    public function getBatchType(FacetBatch $batch) {
        return $this->batchTypes->get($batch->getType());
    }

    /**
     * @return SupportedFilters
     */
    public function getSupportedFilters() {
        return $this->filterTypes;
    }

    public function run(ProductCollection $productCollection) {
        $this->init($productCollection);
        try {
            $this->requestFacets();
            $this->runQueries();
            $this->filterProducts();
            $this->populateFacets();
        } finally {
            $this->clear();
        }
    }

    protected function init($collection) {
        $this->collection = $collection;

        $callback = $this->getApplyAllFiltersCallback();
        $this->mainKey = ':' . $this->mysqlQueryEngine->getRecursiveFilterSignature(
                $this->collection->getQuery()->getFilters(),
                $callback
            );

        $this->queries = [$this->mainKey => $query = $this->initQuery(true, $callback)];
        $query->body[$this->fieldsClause] = ['_id', '_score'];

        $this->facetQueryKeys = [];
        $this->responses = [];
        $this->batches = [];
    }

    protected function clear() {
        $this->collection = null;
        $this->mainKey = null;
        $this->queries = null;
        $this->facetQueryKeys = null;
        $this->responses = null;
        $this->productIds = null;
        $this->batches = null;
    }


    protected function requestFacets() {
        foreach ($this->collection->getQuery()->getFacets() as $facet) {
            if ($this->getFacetType($facet)->preparationNeeded) {
                $this->prepareFacet($facet);
            }
            $this->requestFacet($facet);
        }
    }


    protected function runQueries() {
        $client = $this->connectionManager->getConnection();
        foreach ($this->queries as $key => $query) {
            $this->responses[$key] = $client->query((array)$this->queries[$key]);
        }
    }

    protected function filterProducts() {
        $searchResultTable = $this->temporaryResource->createSearchResultTable();

        if (!empty($this->getProductIds())) {
            $this->temporaryResource->getConnection()->insertArray(
                $searchResultTable,
                ['entity_id', 'score'],
                $this->getProductIdsAndScores()
            );
        }

        $this->collection->getSelect()
            ->joinInner(['search_result' => $searchResultTable], "`e`.`entity_id` = `search_result`.`entity_id`", null);
    }

    protected function populateFacets() {
        foreach ($this->collection->getQuery()->getFacets() as $facet) {
            if ($batch = $this->getFacetType($facet)->populate($facet)) {
                if (!isset($this->batches[$batch])) {
                    $this->batches[$batch] = $this->factory->createFacetBatch($batch);
                }
                $this->batches[$batch]->addFacet($facet);
                continue;
            }
        }

        foreach ($this->batches as $batch) {
            $this->getBatchType($batch)->populate($batch);
        }

        foreach ($this->collection->getQuery()->getFacets() as $facet) {
            if ($facet->getData()) {
                $this->getFacetType($facet)->sort($facet);
            }
        }
    }

    protected function initQuery($requestProducts, callable $callback, $categoryId = null) {
        $query = (object)[
            'index' => $this->getIndexName(),
            'type' => 'document',
            'body' => [
                'from' => 0,
                'size' => $requestProducts ? 10000 : 0,
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['term' => ['category_ids' => $categoryId ?: $this->getCategoryId()]],
                            ['terms' => ['visibility' => ['2', '4']]],
                        ],
                    ],
                ],
            ],
        ];

        return $this->applyFilters($query, $callback);
    }

    protected function getIndexName() {
        return "{$this->elasticConfig->getIndexPrefix()}_product_{$this->collection->getStoreId()}";
    }

    protected function getCategoryId() {
        return $this->collection->getQuery()->getCategory()->getId();
    }

    public function getFacet($name) {
        return $this->collection->getQuery()->getFacet($name);
    }

    protected function applyFilters($query, callable $callback) {
        $this->callback = $callback;
        $this->currentQuery = $query;
        try {
            $this->applyFilterRecursively($this->collection->getQuery()->getFilters());
        } finally {
            $this->callback = null;
            $this->currentQuery = null;
        }

        return $query;
    }

    public function applyFilterRecursively(Filter $filter) {
        if ($this->callback && !call_user_func($this->callback, $filter)) {
            return;
        }

        $this->getFilterType($filter)->apply($filter);
    }

    protected function requestFacet(Facet $facet) {

        $this->currentQuery = $this->getFacetQuery($facet);
        try {
            $this->getFacetType($facet)->request($facet);
        } finally {
            $this->currentQuery = null;
        }
    }

    protected function getFacetQuery(Facet $facet) {
        $key = $this->getFacetQueryKey($facet);

        if (!isset($this->queries[$key])) {
            $facetType = $this->getFacetType($facet);
            $callback = $facetType->getFilterCallback($facet)
                ?: $this->getApplyAllFiltersCallback();
            $this->queries[$key] = $this->initQuery(
                false,
                $callback,
                $facetType->getFacetCategoryId($facet)
            );
        }

        return $this->queries[$key];
    }

    protected function getFacetQueryKey(Facet $facet) {
        if (!isset($this->facetQueryKeys[$facet->getName()])) {
            $facetType = $this->getFacetType($facet);
            $callback = $facetType->getFilterCallback($facet);
            $filter = $this->collection->getQuery()->getFilters();
            $this->facetQueryKeys[$facet->getName()] =
                "{$facetType->getFacetCategoryId($facet)}:" .
                $this->mysqlQueryEngine->getRecursiveFilterSignature($filter, $callback);
        }

        return $this->facetQueryKeys[$facet->getName()];
    }

    protected function prepareFacet(Facet $facet) {
        $this->currentQuery = $this->getFacetPreparationQuery($facet);
        try {
            $this->getFacetType($facet)->prepare($facet);
        } finally {
            $this->currentQuery = null;
        }
    }

    protected function getFacetPreparationQuery(Facet $facet) {
        $key = $this->getFacetPreparationQueryKey($facet);

        if (!isset($this->queries[$key])) {
            $callback = $this->getFacetType($facet)->getPreparationFilterCallback($facet)
                ?: $this->getApplyAllFiltersCallback();
            $this->queries[$key] = $this->initQuery(false, $callback);
        }

        return $this->queries[$key];
    }

    protected function getFacetPreparationQueryKey(Facet $facet) {
        if (!isset($this->facetPreparationQueryKeys[$facet->getName()])) {
            $callback = $this->getFacetType($facet)->getPreparationFilterCallback($facet);
            $filter = $this->collection->getQuery()->getFilters();
            $this->facetPreparationQueryKeys[$facet->getName()] =
                $this->mysqlQueryEngine->getRecursiveFilterSignature($filter, $callback);
        }

        return $this->facetPreparationQueryKeys[$facet->getName()];
    }


    public function getApplyAllFiltersCallback() {
        return function ($filter) {
            return true;
        };
    }

    public function getApplyNoFiltersCallback() {
        return function ($filter) {
            return false;
        };
    }

    public function getPreparationResponse(Facet $facet) {
        return $this->responses[$this->getFacetPreparationQueryKey($facet)];
    }

    public function getResponse(Facet $facet) {
        return $this->responses[$this->getFacetQueryKey($facet)];
    }

    public function getProductIds() {
        if (!$this->productIds) {
            $this->productIds = [];

            $response = $this->responses[$this->mainKey];
            if (!isset($response['hits']['hits'])) {
                return $this->productIds;
            }

            foreach ($response['hits']['hits'] as $hit) {
                $this->productIds[] = $hit['_id'];
            }
        }

        return $this->productIds;
    }

    public function getProductIdsAndScores() {
        if (!$this->productIdsAndScores) {
            $this->productIdsAndScores = [];

            $response = $this->responses[$this->mainKey];
            if (!isset($response['hits']['hits'])) {
                return $this->productIdsAndScores;
            }

            foreach ($response['hits']['hits'] as $hit) {
                $this->productIdsAndScores[] = [$hit['_id'], $hit['_score']];
            }
        }

        return $this->productIdsAndScores;
    }

    public function isEnabled() {
        return $this->features->isEnabled(__CLASS__);
    }
}
