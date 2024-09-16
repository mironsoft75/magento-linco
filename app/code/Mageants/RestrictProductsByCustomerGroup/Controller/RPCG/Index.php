<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Controller\RPCG;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mageants\RestrictProductsByCustomerGroup\Model\RPCGFactory $rpcgFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $context->getResultFactory();
        $this->_rpcgFactory = $rpcgFactory;
        $this->_session = $session;
        $this->_request = $context->getRequest();
        $this->_redirect = $redirect;
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        ;
        $this->_objectManager = $context->getObjectManager();
        $this->context = $context;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $storeid = $this->_storeManager->getStore()->getId();
        $customerGroupid =$this->_session->getCustomer()->getGroupId();
        
        $rulecollection = $this->_rpcgFactory->create()->getCollection();
        $ruledata = '';
        foreach ($rulecollection->getData() as $rule) {
            if ($rule['id'] == $this->_request->getParam('id')) {
                $ruledata = $rule;
            }
        }
        
        $error = 0;
        if ($ruledata['rpcgstatus'] == 'Enabled') {
            $currentDate = date('Y-m-d H:i:s');
            if (($currentDate >= $rule['start_at']) && ($currentDate < $rule['end_at'])) {
                if ($rule['store_id']) {
                    $rule['store_id'] = explode(",", $rule['store_id']);
                    foreach ($rule['store_id'] as $value) {
                        if ($storeid == $value) {
                            if ($rule['cgid']) {
                                $rule['cgid'] = explode(",", $rule['cgid']);
                                foreach ($rule['cgid'] as $value) {
                                    //var_dump($this->_customerSession->isLoggedIn());
                                    if (!$this->_session->isLoggedIn()) {
                                        if ($ruledata['category_ids']) {
                                            $category_ids = explode(",", $ruledata['category_ids']);
                                            foreach ($category_ids as $value) {
                                                if ($value == $this->_request->getParam('categoryid')) {
                                                    $error ++;
                                                }
                                            }
                                        }
                                        if ($ruledata['cpid']) {
                                            $CmsPage = explode(",", $ruledata['cpid']);
                                            foreach ($CmsPage as $value) {
                                                if ($value == $this->_request->getParam('pageid')) {
                                                    $error ++;
                                                }
                                            }
                                        }
                                    } elseif ($value == $customerGroupid) {
                                        if ($ruledata['category_ids']) {
                                            $category_ids = explode(",", $ruledata['category_ids']);
                                            foreach ($category_ids as $value) {
                                                if ($value == $this->_request->getParam('categoryid')) {
                                                    $error ++;
                                                }
                                            }
                                        }
                                        if ($ruledata['cpid']) {
                                            $CmsPage = explode(",", $ruledata['cpid']);
                                            foreach ($CmsPage as $value) {
                                                if ($value == $this->_request->getParam('pageid')) {
                                                    $error ++;
                                                }
                                            }
                                        }
                                    }
                                }
                            } else {
                                //echo $rule['cgid'];
                                if (!$this->_session->isLoggedIn()) {
                                    if ($ruledata['category_ids']) {
                                        $category_ids = explode(",", $ruledata['category_ids']);
                                        foreach ($category_ids as $value) {
                                            if ($value == $this->_request->getParam('categoryid')) {
                                                $error ++;
                                            }
                                        }
                                    }
                                    if ($ruledata['cpid']) {
                                        $CmsPage = explode(",", $ruledata['cpid']);
                                        foreach ($CmsPage as $value) {
                                            if ($value == $this->_request->getParam('pageid')) {
                                                $error ++;
                                            }
                                        }
                                    }
                                } elseif ($rule['cgid'] == $customerGroupid) {
                                    if ($ruledata['category_ids']) {
                                        $category_ids = explode(",", $ruledata['category_ids']);
                                        foreach ($category_ids as $value) {
                                            if ($value == $this->_request->getParam('categoryid')) {
                                                $error ++;
                                            }
                                        }
                                    }
                                    if ($ruledata['cpid']) {
                                        $CmsPage = explode(",", $ruledata['cpid']);
                                        foreach ($CmsPage as $value) {
                                            if ($value == $this->_request->getParam('pageid')) {
                                                $error ++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($rule['store_id'] == 0) {
                    if ($rule['cgid']) {
                        $rule['cgid'] = explode(",", $rule['cgid']);
                        foreach ($rule['cgid'] as $value) {
                            //var_dump($this->_customerSession->isLoggedIn());
                            if (!$this->_session->isLoggedIn()) {
                                if ($ruledata['category_ids']) {
                                    $category_ids = explode(",", $ruledata['category_ids']);
                                    foreach ($category_ids as $value) {
                                        if ($value == $this->_request->getParam('categoryid')) {
                                            $error ++;
                                        }
                                    }
                                }
                                if ($ruledata['cpid']) {
                                    $CmsPage = explode(",", $ruledata['cpid']);
                                    foreach ($CmsPage as $value) {
                                        if ($value == $this->_request->getParam('pageid')) {
                                            $error ++;
                                        }
                                    }
                                }
                            } elseif ($value == $customerGroupid) {
                                if ($ruledata['category_ids']) {
                                    $category_ids = explode(",", $ruledata['category_ids']);
                                    foreach ($category_ids as $value) {
                                        if ($value == $this->_request->getParam('categoryid')) {
                                            $error ++;
                                        }
                                    }
                                }
                                if ($ruledata['cpid']) {
                                    $CmsPage = explode(",", $ruledata['cpid']);
                                    foreach ($CmsPage as $value) {
                                        if ($value == $this->_request->getParam('pageid')) {
                                            $error ++;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        //echo $rule['cgid'];
                        if (!$this->_session->isLoggedIn()) {
                            if ($ruledata['category_ids']) {
                                $category_ids = explode(",", $ruledata['category_ids']);
                                foreach ($category_ids as $value) {
                                    if ($value == $this->_request->getParam('categoryid')) {
                                        $error ++;
                                    }
                                }
                            }
                            if ($ruledata['cpid']) {
                                $CmsPage = explode(",", $ruledata['cpid']);
                                foreach ($CmsPage as $value) {
                                    if ($value == $this->_request->getParam('pageid')) {
                                        $error ++;
                                    }
                                }
                            }
                        } elseif ($rule['cgid'] == $customerGroupid) {
                            if ($ruledata['category_ids']) {
                                $category_ids = explode(",", $ruledata['category_ids']);
                                foreach ($category_ids as $value) {
                                    if ($value == $this->_request->getParam('categoryid')) {
                                        $error ++;
                                    }
                                }
                            }
                            if ($ruledata['cpid']) {
                                $CmsPage = explode(",", $ruledata['cpid']);
                                foreach ($CmsPage as $value) {
                                    if ($value == $this->_request->getParam('pageid')) {
                                        $error ++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $resultPage = $this->resultPageFactory->create();
        
        if ($error != 0) {
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } else {
            if ($this->_request->getParam('categoryid')) {
                $category = $this->_categoryFactory->create()->load($this->_request->getParam('categoryid'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($category->getUrl());
                return $resultRedirect;
            } elseif ($this->_request->getParam('pageid')) {
                $CMSPageURL = $this->_objectManager->create('Magento\Cms\Helper\Page')->getPageUrl($this->_request->getParam('pageid'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($CMSPageURL);
                return $resultRedirect;
            }
        }
        return $resultPage;
    }
}
