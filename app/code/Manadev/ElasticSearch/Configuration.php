<?php

namespace Manadev\ElasticSearch;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Configuration
{
    const ELASTIC_QUERY_LOGGING = 'mana_core/log/elastic_queries';
    const ELASTIC_SEARCH_SKU_AND_NAME_BY_PREFIX = 'mana_layered_navigation/other/elastic_prefix_search';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isElasticQueryLoggingEnabled() {
        return $this->scopeConfig->isSetFlag(static::ELASTIC_QUERY_LOGGING);
    }

    public function searchSkuAndNameByPrefix() {
        return $this->scopeConfig->isSetFlag(static::ELASTIC_SEARCH_SKU_AND_NAME_BY_PREFIX);
    }

}
