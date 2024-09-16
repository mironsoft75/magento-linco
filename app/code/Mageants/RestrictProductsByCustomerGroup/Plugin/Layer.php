<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Plugin;

class Layer
{
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Cms\Model\Page $page,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mageants\RestrictProductsByCustomerGroup\Helper\Data $helper,
        \Mageants\RestrictProductsByCustomerGroup\Model\RPCGFactory $rpcgFactory
    ) {
        $this->_registry = $registry;
        $this->_rpcgFactory = $rpcgFactory;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->_page = $page;
        $this->helper=$helper;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
    }

    public function afterGetProductCollection($subject, $collection)
    {
        $isEnabled=$this->helper->isRPCGEnabled();
        
        if ($isEnabled == 1) {
            $customerGroupid =$this->_customerSession->getCustomer()->getGroupId();
            
            $storeid = $this->_storeManager->getStore()->getId();
            
            $modeldata = $this->_rpcgFactory->create()->getCollection()->setOrder('priority', 'ASC');
            
            foreach ($modeldata->getData() as $rule) {
                
                $rule['cgid'] = explode(",", $rule['cgid']);

                if ($rule['rpcgstatus'] == 'Enabled') {
                    $currentDate = date('Y-m-d H:i:s');
                    if (($currentDate >= $rule['start_at']) && ($currentDate < $rule['end_at'])) {
                        if ($rule['store_id']) {
                            $rule['store_id'] = explode(",", $rule['store_id']);
                            foreach ($rule['store_id'] as $value) {
                                if ($storeid == $value) {
                                    if (!is_array($rule['cgid'])) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            if ($rule['cgid'] == 0) {
                                                $sku = explode(",", $rule['productids']);
                                                $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                            }
                                        } elseif ($rule['cgid'] == $customerGroupid) {
                                            $sku = explode(",", $rule['productids']);
                                            $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                        }
                                    }
                                    if ($rule['cgid']) {
                                        
                                        $sku = explode(",", $rule['productids']);
                                        if(in_array($customerGroupid, $rule['cgid'])){
                                            $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                        }

                                        // $rule['cgid'] = explode(",", $rule['cgid']);
                                        // foreach ($rule['cgid'] as $value) {
                                        //     if (!$this->_customerSession->isLoggedIn()) {
                                        //         if ($value == 0) {
                                        //             $sku = explode(",", $rule['productids']);
                                        //             $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                        //         }
                                        //     } elseif ($value == $customerGroupid) {
                                        //         $sku = explode(",", $rule['productids']);
                                        //         $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                        //     }
                                        // }
                                    }
                                }
                            }
                        } elseif ($rule['store_id'] == 0) {
                            if (!is_array($rule['cgid'])) {
                                if (!$this->_customerSession->isLoggedIn()) {
                                    if ($rule['cgid'] == 0) {
                                        $sku = explode(",", $rule['productids']);
                                        $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                    }
                                } elseif ($rule['cgid'] == $customerGroupid) {
                                    $sku = explode(",", $rule['productids']);
                                    $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                }
                            }
                            if ($rule['cgid']) {
                                // $rule['cgid'] = explode(",", $rule['cgid']);
                                foreach ($rule['cgid'] as $value) {
                                    if (!$this->_customerSession->isLoggedIn()) {
                                        if ($value == 0) {
                                            $sku = explode(",", $rule['productids']);
                                            $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                        }
                                    } elseif ($value == $customerGroupid) {
                                        $sku = explode(",", $rule['productids']);
                                        $collection->addAttributeToFilter('entity_id', array('nin' => $sku));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $collection;
    }
}
