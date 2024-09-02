<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\ProductCollection\Resources\Filters;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Manadev\ProductCollection\Contracts\Filter;
use Manadev\ProductCollection\Contracts\FilterResource;
use Manadev\ProductCollection\Filters\SearchFilter;
use Magento\Store\Model\StoreManagerInterface;
use Manadev\ProductCollection\Factory;
use Magento\Framework\Model\ResourceModel\Db;
use Manadev\ProductCollection\Resources\HelperResource;
use Magento\Framework\App\ObjectManager;


class SearchFilterResource extends FilterResource
{
    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $searchEngine;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Mapper
     */
    protected $mapper;

    protected $resultTables = [];
    protected $searchCriteriaBuilder;
protected $filterBuilder;
protected $search;
protected $_scopeConfig;


    public function __construct(Db\Context $context, Factory $factory,
        StoreManagerInterface $storeManager, HelperResource $helperResource,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        //\Magento\Framework\Search\Adapter\Mysql\Mapper $mapper,
	   \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,

        $resourcePrefix = null)
    {
        parent::__construct($context, $factory, $storeManager, $helperResource, $resourcePrefix);
        $this->searchEngine = $searchEngine;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
	$this->_scopeConfig = $scopeConfig;

       // $this->mapper = $mapper;
    }




    public function getSearchCriteriaBuilder()
    {
        if ($this->searchCriteriaBuilder === null) {
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get('\Magepow\Layerednav\Model\Search\SearchCriteriaBuilder');
        }

        return $this->searchCriteriaBuilder;
    }
   private function getFilterBuilder()
    {
        if ($this->filterBuilder === null) {
            $this->filterBuilder = ObjectManager::getInstance()->get('\Magento\Framework\Api\FilterBuilder');
        }
    
        return $this->filterBuilder;
    }   
    
   private function getSearch()
    {
        if ($this->search === null) {
            $this->search = ObjectManager::getInstance()->get('\Magento\Search\Api\SearchInterface');
        } 
            
        return $this->search;
    }  
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct() {
        $this->_setMainTable('catalogsearch_fulltext_scope' . $this->getStoreId());
    }


    /**
     * @param Select $select
     * @param Filter $filter
     * @param $callback
     * @return false|string|void
     * @throws \Zend_Db_Exception
     */
    public function apply2(Select $select, Filter $filter, $callback) {
        /** @var $filter SearchFilter */

        $key = $this->getStoreId() . '-' . $filter->getText();
        if (!isset($this->resultTables[$key])) {
            $requestBuilder = $this->factory->createRequestBuilder();
            $requestBuilder->bindDimension('scope', $this->getStoreId());
            $requestBuilder->bind('search_term', $filter->getText());
            $requestBuilder->setRequestName('quick_search_container');
            $request = $requestBuilder->create();

            //$query = $this->mapper->buildQuery($request);
            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocumentsFromSelect($query);

            $this->resultTables[$key] = $table->getName();
        }

        $select->joinInner(['search_result' => $this->resultTables[$key]],
            'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID, []);
    }


public function apply(Select $select, Filter $filter, $callback) {
    /** @var $filter SearchFilter */

    $this->getSearchCriteriaBuilder();
    $this->getFilterBuilder();
    $this->getSearch();

    // Agregar filtro de tÃ©rmino de bÃºsqueda
    if ($filter->getText()) {
	    die();
        $this->filterBuilder->setField('search_term');
        $this->filterBuilder->setValue($filter->getText());
	die();
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }
    //die();

    // Agregar otros filtros como el rango de precios dinÃ¡mico
    //$priceRangeCalculation = $this->_scopeConfig->getValue(
    //    \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
    //    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
   // );
    if ($priceRangeCalculation) {
        $this->filterBuilder->setField('price_dynamic_algorithm');
        $this->filterBuilder->setValue($priceRangeCalculation);
        $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
    }

    // Crear el criterio de bÃºsqueda
    $searchCriteria = $this->searchCriteriaBuilder->create();
    $searchCriteria->setRequestName('quick_search_container');

    // Ejecutar la bÃºsqueda
    try {
        $this->searchResult = $this->getSearch()->search($searchCriteria);
    } catch (EmptyRequestDataException $e) {
        $this->searchResult = $this->searchResultFactory->create()->setItems([]);
    } catch (NonExistingRequestNameException $e) {
        $this->_logger->error($e->getMessage());
        throw new LocalizedException(__('Sorry, something went wrong. You can find out more in the error log.'));
    }

    // Almacenar los documentos en una tabla temporal
    $temporaryStorage = $this->temporaryStorageFactory->create();
    $table = $temporaryStorage->storeApiDocuments($this->searchResult->getItems());

    // Unir la tabla temporal con la consulta principal
    $select->joinInner(
        ['search_result' => $table->getName()],
        'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
        []
    );

    // Ordenar los resultados por relevancia si estÃ¡ configurado
    if ($this->order && 'relevance' === $this->order['field']) {
        $select->order('search_result.' . TemporaryStorage::FIELD_SCORE . ' ' . $this->order['dir']);
    }
}


}
