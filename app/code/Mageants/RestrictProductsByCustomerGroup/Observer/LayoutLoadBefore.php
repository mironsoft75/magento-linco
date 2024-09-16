<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\RestrictProductsByCustomerGroup\Observer;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Cms\Model\Page $page,
        \Mageants\RestrictProductsByCustomerGroup\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
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


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isEnabled=$this->helper->isRPCGEnabled();

        if ($isEnabled == 1) {
            // Get Customer Group //
            $customerGroupid =$this->_customerSession->getCustomer()->getGroupId();
            // Get Store Id //
            $storeid = $this->_storeManager->getStore()->getId();
            // Get Model Data //
            $modeldata = $this->_rpcgFactory->create()->getCollection()->setOrder('priority', 'ASC');
            // Get Current Product //
            $product = $this->_registry->registry('current_product');
            // Get Current Product //
            $category = $this->_registry->registry('current_category');
          
            $event = $observer->getEvent();
          
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
                                                    // Product
                                                    if ($rule['productids']) {
                                                        $productidsarray = explode(",", $rule['productids']);
                                                        foreach ($productidsarray as $value) {
                                                            if ($product) {
                                                                if ($value == $product->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Category
                                                    if ($rule['category_ids']) {
                                                        $categoryidarray = explode(",", $rule['category_ids']);
                                                        if ($category) {
                                                            foreach ($categoryidarray as $value) {
                                                                if ($value == $category->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // CMS Page
                                                    if ($rule['cpid']) {
                                                        $CmsPageidarray = explode(",", $rule['cpid']);
                                                        foreach ($CmsPageidarray as $value) {
                                                            if ($value == $this->_page->getId()) {
                                                                if ($rule['response'] == 0) {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                                    return $this;
                                                                } else {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Url
                                                    if ($rule['url']) {
                                                        $rule['url'] = explode(",", $rule['url']);
                                                        foreach ($rule['url'] as $value) {
                                                            $singlerule = explode(";", $value);
                                                            if ($singlerule) {
                                                                if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                                    $event = $observer->getEvent();
                                                                    $this->_redirect->setRedirect($singlerule[1]);
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } elseif ($value == $customerGroupid) {
                                                if ($this->_customerSession->isLoggedIn()) {
                                                    // Product
                                                    if ($rule['productids']) {
                                                        $productidsarray = explode(",", $rule['productids']);
                                                        foreach ($productidsarray as $value) {
                                                            if ($product) {
                                                                if ($value == $product->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Category
                                                    if ($rule['category_ids']) {
                                                        $categoryidarray = explode(",", $rule['category_ids']);
                                                        if ($category) {
                                                            foreach ($categoryidarray as $value) {
                                                                if ($value == $category->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // CMS Page
                                                    if ($rule['cpid']) {
                                                        $CmsPageidarray = explode(",", $rule['cpid']);
                                                        foreach ($CmsPageidarray as $value) {
                                                            if ($value == $this->_page->getId()) {
                                                                if ($rule['response'] == 0) {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                                    return $this;
                                                                } else {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Url
                                                    if ($rule['url']) {
                                                        $rule['url'] = explode(",", $rule['url']);
                                                        foreach ($rule['url'] as $value) {
                                                            $singlerule = explode(";", $value);
                                                            if ($singlerule) {
                                                                if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                                    $event = $observer->getEvent();
                                                                    $this->_redirect->setRedirect($singlerule[1]);
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($rule['cgid'] == 0) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            // Product
                                            if ($rule['productids']) {
                                                $productidsarray = explode(",", $rule['productids']);
                                                foreach ($productidsarray as $value) {
                                                    if ($product) {
                                                        if ($value == $product->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // Category
                                            if ($rule['category_ids']) {
                                                $categoryidarray = explode(",", $rule['category_ids']);
                                                if ($category) {
                                                    foreach ($categoryidarray as $value) {
                                                        if ($value == $category->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // CMS Page
                                            if ($rule['cpid']) {
                                                $CmsPageidarray = explode(",", $rule['cpid']);
                                                foreach ($CmsPageidarray as $value) {
                                                    if ($value == $this->_page->getId()) {
                                                        if ($rule['response'] == 0) {
                                                            $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                            return $this;
                                                        } else {
                                                            $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }

                                            // Url
                                            if ($rule['url']) {
                                                $rule['url'] = explode(",", $rule['url']);
                                                foreach ($rule['url'] as $value) {
                                                    $singlerule = explode(";", $value);
                                                    if ($singlerule) {
                                                        if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                            $event = $observer->getEvent();
                                                            $this->_redirect->setRedirect($singlerule[1]);
                                                            return $this;
                                                        }
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
                                                    // Product
                                                    if ($rule['productids']) {
                                                        $productidsarray = explode(",", $rule['productids']);
                                                        foreach ($productidsarray as $value) {
                                                            if ($product) {
                                                                if ($value == $product->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Category
                                                    if ($rule['category_ids']) {
                                                        $categoryidarray = explode(",", $rule['category_ids']);
                                                        if ($category) {
                                                            foreach ($categoryidarray as $value) {
                                                                if ($value == $category->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // CMS Page
                                                    if ($rule['cpid']) {
                                                        $CmsPageidarray = explode(",", $rule['cpid']);
                                                        foreach ($CmsPageidarray as $value) {
                                                            if ($value == $this->_page->getId()) {
                                                                if ($rule['response'] == 0) {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                                    return $this;
                                                                } else {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Url
                                                    if ($rule['url']) {
                                                        if (!is_array($rule['url'])) {
                                                            $rule['url'] = explode(",", $rule['url']);
                                                        }
                                                        foreach ($rule['url'] as $value) {
                                                            $singlerule = explode(";", $value);
                                                            if ($singlerule) {
                                                                if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                                    $event = $observer->getEvent();
                                                                    $this->_redirect->setRedirect($singlerule[1]);
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } elseif ($value == $customerGroupid) {
                                                if ($this->_customerSession->isLoggedIn()) {
                                                    // Product
                                                    if ($rule['productids']) {
                                                        $productidsarray = explode(",", $rule['productids']);
                                                        foreach ($productidsarray as $value) {
                                                            if ($product) {
                                                                if ($value == $product->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Category
                                                    if ($rule['category_ids']) {
                                                        $categoryidarray = explode(",", $rule['category_ids']);
                                                        if ($category) {
                                                            foreach ($categoryidarray as $value) {
                                                                if ($value == $category->getId()) {
                                                                    if ($rule['response'] == 0) {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                        return $this;
                                                                    } else {
                                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                        return $this;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // CMS Page
                                                    if ($rule['cpid']) {
                                                        $CmsPageidarray = explode(",", $rule['cpid']);
                                                        foreach ($CmsPageidarray as $value) {
                                                            if ($value == $this->_page->getId()) {
                                                                if ($rule['response'] == 0) {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                                    return $this;
                                                                } else {
                                                                    $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }

                                                    // Url
                                                    if ($rule['url']) {
                                                        if (!is_array($rule['url'])) {
                                                            $rule['url'] = explode(",", $rule['url']);
                                                        }
                                                        foreach ($rule['url'] as $value) {
                                                            $singlerule = explode(";", $value);
                                                            if ($singlerule) {
                                                                if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                                    $event = $observer->getEvent();
                                                                    $this->_redirect->setRedirect($singlerule[1]);
                                                                    return $this;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($rule['cgid'] == 0) {
                                        if (!$this->_customerSession->isLoggedIn()) {
                                            // Product
                                            if ($rule['productids']) {
                                                $productidsarray = explode(",", $rule['productids']);
                                                foreach ($productidsarray as $value) {
                                                    if ($product) {
                                                        if ($value == $product->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // Category
                                            if ($rule['category_ids']) {
                                                $categoryidarray = explode(",", $rule['category_ids']);
                                                if ($category) {
                                                    foreach ($categoryidarray as $value) {
                                                        if ($value == $category->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // CMS Page
                                            if ($rule['cpid']) {
                                                $CmsPageidarray = explode(",", $rule['cpid']);
                                                foreach ($CmsPageidarray as $value) {
                                                    if ($value == $this->_page->getId()) {
                                                        if ($rule['response'] == 0) {
                                                            $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                            return $this;
                                                        } else {
                                                            $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }

                                            // Url
                                            if ($rule['url']) {
                                                if (!is_array($rule['url'])) {
                                                    $rule['url'] = explode(",", $rule['url']);
                                                }
                                                foreach ($rule['url'] as $value) {
                                                    $singlerule = explode(";", $value);
                                                    if ($singlerule) {
                                                        if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                            $event = $observer->getEvent();
                                                            $this->_redirect->setRedirect($singlerule[1]);
                                                            return $this;
                                                        }
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
                                            // Product
                                            if ($rule['productids']) {
                                                $productidsarray = explode(",", $rule['productids']);
                                                foreach ($productidsarray as $value) {
                                                    if ($product) {
                                                        if ($value == $product->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // Category
                                            if ($rule['category_ids']) {
                                                $categoryidarray = explode(",", $rule['category_ids']);
                                                if ($category) {
                                                    foreach ($categoryidarray as $value) {
                                                        if ($value == $category->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // CMS Page
                                            if ($rule['cpid']) {
                                                $CmsPageidarray = explode(",", $rule['cpid']);
                                                foreach ($CmsPageidarray as $value) {
                                                    if ($value == $this->_page->getId()) {
                                                        if ($rule['response'] == 0) {
                                                            $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                            return $this;
                                                        } else {
                                                            $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }

                                            // Url
                                            if ($rule['url']) {
                                                $rule['url'] = explode(",", $rule['url']);
                                                foreach ($rule['url'] as $value) {
                                                    $singlerule = explode(";", $value);
                                                    if ($singlerule) {
                                                        if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                            $event = $observer->getEvent();
                                                            $this->_redirect->setRedirect($singlerule[1]);
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } elseif ($value == $customerGroupid) {
                                        if ($this->_customerSession->isLoggedIn()) {
                                            // Product
                                            if ($rule['productids']) {
                                                $productidsarray = explode(",", $rule['productids']);
                                                foreach ($productidsarray as $value) {
                                                    if ($product) {
                                                        if ($value == $product->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // Category
                                            if ($rule['category_ids']) {
                                                $categoryidarray = explode(",", $rule['category_ids']);
                                                if ($category) {
                                                    foreach ($categoryidarray as $value) {
                                                        if ($value == $category->getId()) {
                                                            if ($rule['response'] == 0) {
                                                                $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                                return $this;
                                                            } else {
                                                                $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                                return $this;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            // CMS Page
                                            if ($rule['cpid']) {
                                                $CmsPageidarray = explode(",", $rule['cpid']);
                                                foreach ($CmsPageidarray as $value) {
                                                    if ($value == $this->_page->getId()) {
                                                        if ($rule['response'] == 0) {
                                                            $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                            return $this;
                                                        } else {
                                                            $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }

                                            // Url
                                            if ($rule['url']) {
                                                $rule['url'] = explode(",", $rule['url']);
                                                foreach ($rule['url'] as $value) {
                                                    $singlerule = explode(";", $value);
                                                    if ($singlerule) {
                                                        if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                            $event = $observer->getEvent();
                                                            $this->_redirect->setRedirect($singlerule[1]);
                                                            return $this;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($rule['cgid'] == 0) {
                                if (!$this->_customerSession->isLoggedIn()) {
                                    // Product
                                    if ($rule['productids']) {
                                        $productidsarray = explode(",", $rule['productids']);
                                        foreach ($productidsarray as $value) {
                                            if ($product) {
                                                if ($value == $product->getId()) {
                                                    if ($rule['response'] == 0) {
                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type'=> 'Products')));
                                                        return $this;
                                                    } else {
                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                        return $this;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // Category
                                    if ($rule['category_ids']) {
                                        $categoryidarray = explode(",", $rule['category_ids']);
                                        if ($category) {
                                            foreach ($categoryidarray as $value) {
                                                if ($value == $category->getId()) {
                                                    if ($rule['response'] == 0) {
                                                        $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'Category','categoryid' => $category->getId())));
                                                        return $this;
                                                    } else {
                                                        $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                        return $this;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    // CMS Page
                                    if ($rule['cpid']) {
                                        $CmsPageidarray = explode(",", $rule['cpid']);
                                        foreach ($CmsPageidarray as $value) {
                                            if ($value == $this->_page->getId()) {
                                                if ($rule['response'] == 0) {
                                                    $this->_redirect->setRedirect($this->_url->getUrl('rpcg/rpcg/', array('id' => $rule['id'],'type' => 'CMS_Page','pageid'=> $this->_page->getId())));
                                                    return $this;
                                                } else {
                                                    $this->_redirect->setRedirect($this->_url->getUrl($rule['redirectoption'], array('id' => $rule['id'])));
                                                    return $this;
                                                }
                                            }
                                        }
                                    }

                                    // Url
                                    if ($rule['url']) {
                                        $rule['url'] = explode(",", $rule['url']);
                                        foreach ($rule['url'] as $value) {
                                            $singlerule = explode(";", $value);
                                            if ($singlerule) {
                                                if (trim($singlerule[0]) == trim($this->_url->getCurrentUrl())) {
                                                    $event = $observer->getEvent();
                                                    $this->_redirect->setRedirect($singlerule[1]);
                                                    return $this;
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
        }
        return $this;
    }
}
