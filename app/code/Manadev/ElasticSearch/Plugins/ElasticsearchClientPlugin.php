<?php

namespace Manadev\ElasticSearch\Plugins;

use Magento\Framework\App\ObjectManager;
use Manadev\Core\Logger;
use Manadev\ElasticSearch\Configuration;

class ElasticsearchClientPlugin
{
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    public function aroundQuery($client, callable $proceed, $query) {
        if (!$this->configuration->isElasticQueryLoggingEnabled()) {
            return $proceed($query);
        }

        $objectManager = ObjectManager::getInstance();
        $logger = $objectManager->get(Logger::class); /* @var Logger $logger */

        $logger->debug(sprintf("%s\n\n%s\n",
            json_encode($query, JSON_PRETTY_PRINT),
            str_repeat('-', 80)
        ), ['file' => 'elasticsearch']);

        $result = $proceed($query);

        $logger->debug(sprintf("%s\n\n%s\n",
            json_encode($result, JSON_PRETTY_PRINT),
            str_repeat('=', 80)
        ), ['file' => 'elasticsearch']);

        return $result;
    }
}