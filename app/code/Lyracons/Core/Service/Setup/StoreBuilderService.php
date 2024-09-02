<?php

namespace Lyracons\Core\Service\Setup;

use Lyracons\Core\Api\Setup\StoreBuilderServiceInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class StoreBuilderService implements StoreBuilderServiceInterface
{

    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var GroupFactory
     */
    private $groupFactory;
    /**
     * @var Group
     */
    private $groupResourceModel;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var Store
     */
    private $storeResourceModel;
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var Website
     */
    private $websiteResourceModel;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var array
     */
    private $configData;

    /**
     * InstallData constructor.
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param Store $storeResourceModel
     * @param Group $groupResourceModel
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param ManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        Group $groupResourceModel,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager,
        Store $storeResourceModel,
        StoreFactory $storeFactory,
        Website $websiteResourceModel,
        WebsiteFactory $websiteFactory,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    )
    {
        $this->eventManager = $eventManager;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeFactory = $storeFactory;
        $this->storeResourceModel = $storeResourceModel;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->configData = null;
    }

    /**
     * @param array $data
     * @return StoreBuilderServiceInterface
     * @throws \Exception
     */
    public function create(array $data)
    {
        $website = $this->createWebsite($data);
        $group = $this->createStoreGroup($data, $website);
        $store = $this->createStore($data, $website, $group);
        $this->eventManager->dispatch('store_add', ['store' => $store]);
        $config = $this->getConfigData();
        if (is_null($config)) {
            $config = $this->initConfig($data);
        }
        $this->saveConfig($store->getId(), $config, 'stores');
        $this->saveConfig($website->getId(), $config, 'websites');

        return $this;
    }

    /**
     * @param int $id
     * @param array $config
     * @param string $scope
     * @return $this
     */
    protected function saveConfig($id, $config, $scope)
    {
        if (!isset($config[$scope])) {
            return $this;
        }
        foreach ($config[$scope] as $path => $value) {
            $this->configWriter->save($path, $value, $scope, $id);
        }
        return $this;
    }

    /**
     * @param array $data
     * @return \Magento\Store\Model\Website
     * @throws \Exception
     */
    protected function createWebsite($data)
    {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->websiteFactory->create();
        $website->load($data['website_code']);
        if (!$website->getId()) {
            $website->setCode($data['website_code']);
            $website->setName($data['website_name']);
            $website->save();
        }
        return $website;
    }

    /**
     * @param array $data
     * @param \Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\Group
     * @throws \Exception
     */
    protected function createStoreGroup($data, $website)
    {
        /** @var \Magento\Store\Model\Group $group */
        $group = $this->groupFactory->create();
        $group->load($data['group_name'], 'name');
        if (!$group->getId()) {
            /** @var \Magento\Store\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setWebsiteId($website->getWebsiteId());
            $group->setName($data['group_name']);
            $group->setCode($data['group_code']);
            $group->save();
        }
        return $group;
    }

    /**
     * @param array $data
     * @param $website
     * @param $group
     * @return StoreBuilderServiceInterface|\Magento\Store\Model\Store
     */
    protected function createStore($data, $website, $group)
    {
        /** @var  \Magento\Store\Model\Store $store */
        $store = $this->storeFactory->create();
        $store->setCode($data['store_code']);
        $store->setName($data['store_name']);
        $store->setWebsite($website);
        $store->setGroupId($group->getId());
        $store->setData('is_active', $data['is_active']);
        $store->save();
        return $store;
    }

    /**
     * @param array $data
     * @return array
     */
    private function initConfig(array $data)
    {

        $this->configData = [
            'stores' => [],
            'websites' => [
                'design/theme/theme_id' => $data['theme_id'],
                'design/head/includes' => null,
                'design/head/title_prefix' => null,
                'design/head/title_suffix' => null,
                'design/email/logo_alt' => $data['logo'],
                'design/email/logo_height' => $data['logo'],
                'design/email/logo_width' => $data['logo'],
                'design/header/logo_height' => $data['logo'],
                'design/header/logo_width' => $data['logo'],
                'currency/options/base' => $data['currency'],
                'currency/options/default' => $data['currency'],
                'currency/options/allow' => $data['currency'],
                'general/locale/code' => $data['locale'],
                // Web
                'web/unsecure/base_url' => $data['unsecure_url'],
                'web/secure/base_url' => $data['secure_url'],
                'web/unsecure/base_link_url' => $data['unsecure_url'],
                'web/secure/base_link_url' => $data['secure_url'],
            ],
        ];
        return $this->configData;
    }

    /**
     * @return mixed
     */
    public function getConfigData()
    {
        return $this->configData;
    }

    /**
     * @param mixed $configData
     */
    public function setConfigData($configData)
    {
        $this->configData = $configData;
    }

}