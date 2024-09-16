<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Plugin;

class Block
{
    const IS_ACTIVE  = 'is_active';
    
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSession,
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
        $this->_storeManager = $storeManager;
        $this->helper=$helper;
        $this->_customerSession = $customerSession->create();
    }

    public function afterisActive(\Magento\Cms\Model\Block $subject, $result)
    {
        $isEnabled=$this->helper->isRPCGEnabled();

        if ($isEnabled == 1) {
            // Get Customer Group //
            $customerGroupid =$this->_customerSession->getCustomer()->getGroupId();
            // Get Store Id //
            $storeid = $this->_storeManager->getStore()->getId();
            
            $modeldata = $this->_rpcgFactory->create()->getCollection()->setOrder('priority', 'ASC');
            //echo $subject->getData('block_id')." ::: ";
            foreach ($modeldata->getData() as $rule) {
                if ($rule['rpcgstatus'] == 'Enabled') {
                    $currentDate = date('Y-m-d H:i:s');
                    if (($currentDate >= $rule['start_at']) && ($currentDate < $rule['end_at'])) {
                        if ($rule['store_id']) {
                            $storeidarray = explode(",", $rule['store_id']);
                            foreach ($storeidarray as $value) {
                                if ($value == 0) {
                                    if ($rule['cgid']) {
                                        $customergroupidarray = explode(",", $rule['cgid']);
                                        foreach ($customergroupidarray as $value) {
                                            if ($value == 0) {
                                                if (!$this->_customerSession->isLoggedIn()) {
                                                    if ($rule['blocks']) {
                                                        $blockidarray = explode(",", $rule['blocks']);
                                                        $block_id = $subject->getData('block_id');
                                                        foreach ($blockidarray as $value) {
                                                            if ($value == $block_id) {
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                }
                                            } elseif ($value == $customerGroupid) {
                                                if ($this->_customerSession->isLoggedIn()) {
                                                    if ($rule['blocks']) {
                                                        $blockidarray = explode(",", $rule['blocks']);
                                                        $block_id = $subject->getData('block_id');
                                                        foreach ($blockidarray as $value) {
                                                            if ($value == $block_id) {
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($rule['cgid'] == 0) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            if ($rule['blocks']) {
                                                $blockidarray = explode(",", $rule['blocks']);
                                                $block_id = $subject->getData('block_id');
                                                foreach ($blockidarray as $value) {
                                                    if ($value == $block_id) {
                                                        return false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } elseif ($value == $storeid) {
                                    if ($rule['cgid']) {
                                        $customergroupidarray = explode(",", $rule['cgid']);
                                        foreach ($customergroupidarray as $value) {
                                            if ($value == 0) {
                                                if (!$this->_customerSession->isLoggedIn()) {
                                                    if ($rule['blocks']) {
                                                        $blockidarray = explode(",", $rule['blocks']);
                                                        $block_id = $subject->getData('block_id');
                                                        foreach ($blockidarray as $value) {
                                                            if ($value == $block_id) {
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                }
                                            } elseif ($value == $customerGroupid) {
                                                if ($this->_customerSession->isLoggedIn()) {
                                                    if ($rule['blocks']) {
                                                        $blockidarray = explode(",", $rule['blocks']);
                                                        $block_id = $subject->getData('block_id');
                                                        foreach ($blockidarray as $value) {
                                                            if ($value == $block_id) {
                                                                return false;
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($rule['cgid'] == 0) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            if ($rule['blocks']) {
                                                $blockidarray = explode(",", $rule['blocks']);
                                                $block_id = $subject->getData('block_id');
                                                foreach ($blockidarray as $value) {
                                                    if ($value == $block_id) {
                                                        return false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($rule['store_id'] == 0) {
                            if ($rule['cgid']) {
                                $customergroupidarray = explode(",", $rule['cgid']);
                                foreach ($customergroupidarray as $value) {
                                    if ($value == 0) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            if ($rule['blocks']) {
                                                $blockidarray = explode(",", $rule['blocks']);
                                                $block_id = $subject->getData('block_id');
                                                foreach ($blockidarray as $value) {
                                                    if ($value == $block_id) {
                                                        return false;
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($value == $customerGroupid) {
                                        if ($this->_customerSession->isLoggedIn()) {
                                            if ($rule['blocks']) {
                                                $blockidarray = explode(",", $rule['blocks']);
                                                $block_id = $subject->getData('block_id');
                                                foreach ($blockidarray as $value) {
                                                    if ($value == $block_id) {
                                                        return false;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($rule['cgid'] == 0) {
                                if (!$this->_customerSession->isLoggedIn()) {
                                    if ($rule['blocks']) {
                                        $blockidarray = explode(",", $rule['blocks']);
                                        $block_id = $subject->getData('block_id');
                                        foreach ($blockidarray as $value) {
                                            if ($value == $block_id) {
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return (bool)$subject->getData(self::IS_ACTIVE);
    }
}
