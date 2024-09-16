<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\RestrictProductsByCustomerGroup\Controller\Adminhtml\RPCG;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\TestFramework\ErrorLog\Logger;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $_jsHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryFactory;

    /**
     * \Magento\Backend\Helper\Js $jsHelper
     * @param Action\Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Helper\Js $jsHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
    ) {
        $this->_jsHelper = $jsHelper;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageants_RestrictProductsByCustomerGroup::rpcg_content');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->_request->getParams();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Mageants\RestrictProductsByCustomerGroup\Model\RPCG $model */
            $model = $this->_objectManager->create('Mageants\RestrictProductsByCustomerGroup\Model\RPCG');
            $id = $this->getRequest()->getParam('id');
            

            if (isset($data['store_id'])) {
                if (in_array('0', $data['store_id'])) {
                    $data['store_id'] = implode(",", $data['store_id']);
                } else {
                    $data['store_id'] = implode(",", $data['store_id']);
                }
            }
            
            if (isset($data['productids'])) {
                $data['productids'] = str_replace("&", ",", $data['productids']);
            }

            if (isset($data['blocks'])) {
                $data['blocks'] = str_replace("&", ",", $data['blocks']);
            }
            
            if (isset($data['cgid'])) {
                if (is_array($data['cgid'])) {
                    $data['cgid'] = implode(",", $data['cgid']);
                }
            }

            if (isset($data['cpid'])) {
                if (is_array($data['cpid'])) {
                    $data['cpid'] = implode(",", $data['cpid']);
                }
            }
            /* START SHOW CATEGORY IN FRONTEND */
            $categories = $this->_categoryFactory->create()->addAttributeToSelect('*')->setStore($this->_storeManager->getStore());
            if ($data['show_categories'] == 1) {
                $selectedCateId = array();
                if (isset($data['category_ids'])) {
                    $selectedCateId = explode(",", str_replace(' ', '', $data['category_ids']));
                    if ($categories) {
                        foreach ($categories as $category) {
                            foreach ($selectedCateId as $categoryId) {
                                if ($categoryId == $category->getId()) {
                                    $category->setIncludeInMenu(0);
                                    $category->save();
                                }
                            }
                        }
                        foreach ($categories as $category) {
                            foreach ($selectedCateId as $categoryId) {
                                if ($categoryId != $category->getId()) {
                                    $category->setIncludeInMenu(1);
                                    $category->save();
                                }
                            }
                        }
                    }
                }
            } else {
                $selectedCateId = array();
                if (isset($data['category_ids'])) {
                    $selectedCateId = explode(",", str_replace(' ', '', $data['category_ids']));
                    if ($categories) {
                        foreach ($categories as $category) {
                            foreach ($selectedCateId as $categoryId) {
                                if ($categoryId == $category->getId()) {
                                    $category->setIncludeInMenu(1);
                                    $category->save();
                                }
                            }
                        }
                    }
                }
            }
            /* END SHOW CATEGORY IN FRONTEND */
            $url_contant = "";
            if (isset($data['url'])) {
                $i = 0;
                foreach ($data['url'] as $key => $value) {
                    if (isset($value['from'])) {
                        $i++;
                        $url_contant = $url_contant.$value['from'].';'.$value['to'].',';
                    }
                }

                $data['url'] =$url_contant;
            }
            
            if ($id) {
                $model->load($id);
            }
        
            $model->setData($data);
            try {
                $model->save();

                $this->messageManager->addSuccess(__('You saved this Rule.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', array('id' => $model->getId(), '_current' => true));
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Rule.'.$e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }

        return $resultRedirect->setPath('*/*/');
    }
}
