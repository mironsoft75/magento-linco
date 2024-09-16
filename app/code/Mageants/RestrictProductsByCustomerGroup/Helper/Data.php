<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\RestrictProductsByCustomerGroup\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Catalog\Model\ProductRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\ResourceModel\Group\Collection $groupCollection,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Mageants\RestrictProductsByCustomerGroup\Model\RPCG\Source\StoreList $storeList,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->_search = $searchCriteriaBuilder;
        $this->_objectManager = $objectManager;
        $this->_allStoreList=$storeList;
        $this->_groupcollection=$groupCollection;
        $this->_backendUrl = $backendUrl;
        $this->pageFactory = $pageFactory; 
        $this->scopeConfig = $context->getScopeConfig();
        $this->_productRepository = $productRepository;
    }
    

    /**
     * Get Store List
     * @return Array
     */
    public function getStoreList()
    {
     return $this->_allStoreList->toOptionArray();
    }

    public function isRPCGEnabled(){
        $value= $this->scopeConfig->getValue('mageants_restrictproductsbycustomergroup/restrictproductsbycustomergroup/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $value;
    }

    public function getCgid()
    {
        $groupOptions = $this->_groupcollection->toOptionArray();
        foreach ($groupOptions as $group) {
                $ret[] = array(
                      'value' => $group['value'],
                      'label' => $group['label']
                );
        }

        return $ret;
    }

    public function getCmspage()
    {
        $pages = array();
        $page = $this->pageFactory->create();
        foreach ($page->getCollection() as $page) {
            $pages[] = array(
                'value' => $page->getId(),
                'label' => $page->getTitle()
            );
        }

        return $pages;
    }

    protected function _getSearchCriteria()
    {
        return $this->_search->addFilter('is_active', '1')->create();
    }

    public function unserializeSetting($string)
    {
        $data['setting'] = array();
        
        if (!empty($string)) {
            return unserialize($string);
        } else {
            return $data;
        }
    }

    public function getCategoriesTree()
    {
        $categories = $this->_objectManager->create(
            'Magento\Catalog\Ui\Component\Product\Form\Categories\Options'
        )->toOptionArray();

        return json_encode($categories);
    }

    public function getBlocksGridUrl()
    {
        return $this->_backendUrl->getUrl('rpcg/rpcg/blocks', array('_current' => true));
    }

    public function getProductsGridUrl()
    {
        return $this->_backendUrl->getUrl('rpcg/rpcg/products', array('_current' => true));
    }
}
