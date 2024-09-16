<?php
/**
 * Restrict Order Quantity
 *
 * @category Addify
 * @package  Addify_RestrictOrderQuantity
 * @author   Addify
 * @Email    info@addify.com
 */
namespace Addify\RestrictOrderByCustomer\Plugin;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;
class CartPlugin
{



    public function __construct(
        \Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer\CollectionFactory $restrictorderquantityCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Addify\RestrictOrderByCustomer\Helper\HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement
    ) {
        $this->restrictorderquantityCollection = $restrictorderquantityCollection;
        $this->itemCollection = $itemCollection;
        $this->_productCollectionFactory = $productFactory;
        $this->configurable = $configurable;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->groupManagement = $groupManagement;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->coreSession = $coreSession;



    }


    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {

        if(!$this->helper->isEnabledInFrontend()):
            return [$productInfo, $requestInfo];
        endif;
        
        $minMessage = str_replace('{product_name}',$productInfo->getName(),$this->helper->minMessage());
        $maxMessage = str_replace('{product_name}',$productInfo->getName(),$this->helper->maxMessage());

        $product = $requestInfo['product'];
        $qty = !empty($requestInfo['qty'])?$requestInfo['qty']:1;
        $quoteId = $subject->getQuote()->getId();
        $item = $this->itemCollection->create()->addFieldToFIlter('quote_id',$quoteId)->addFieldToFIlter('product_id',$product)->getFirstItem();
        $totalItems=count($subject->getQuote()->getAllItems());
        

        if($productInfo->getTypeId()=='configurable'):
            if(isset($requestInfo['super_attribute'])):
                $product = $this->configurable->getProductByAttributes($requestInfo['super_attribute'],$productInfo)->getId();
                $itemSup = $this->itemCollection->create()->addFieldToFIlter('quote_id',$quoteId)->addFieldToFIlter('product_id',$product)->getFirstItem();
                $totalItems=count($subject->getQuote()->getAllItems());
            endif;
        endif;
        if($item->getData()):

            if($item->getProduct()->getTypeId()=='configurable'):


                $product = $item->getOptionByCode('simple_product')->getProduct()->getId();
                $qty = $qty+$item->getOptionByCode('simple_product')->getQty();

            else:
                $qty = $qty+$item->getQty();

            endif;
        else:
            if($productInfo->getTypeId()=='configurable'):
                if(isset($requestInfo['super_attribute'])):
                $product = $this->configurable->getProductByAttributes($requestInfo['super_attribute'],$productInfo)->getId();
                endif;

            endif;
        endif;


        if($this->customerSession->isLoggedIn()):
        $customerId = $this->customerSession->getCustomer()->getId();
        $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

        else:
            $customerId = -1;
            $customerGroupId = 0;
        endif;
        $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

        $restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');
        foreach ($restrictorderquantityCollection as $restrictorderCollection)
        {
            $groupCheck = false;
            $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
            $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
            $availableProductIds = explode(',',$restrictorderCollection->getProductIds());
            if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                $groupCheck = true;

            }
            if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {

                if (in_array($product, $availableProductIds) || $restrictorderCollection->getProductType()==1) {


                    if ($qty < $restrictorderCollection->getMinQty()):
                        if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                            $this->coreSession->start();
                            $this->coreSession->setCompareRedirect(true);
                        }
                        throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMinQty(), $minMessage)));

                    elseif ($qty > $restrictorderCollection->getMaxQty()):
                        if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                            $this->coreSession->start();
                            $this->coreSession->setCompareRedirect(true);
                        }
                        throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage)));

                    endif;
                }
            }
        }

        return [$productInfo, $requestInfo];
    }
    public function beforeUpdateItems($subject, $data)
    {


        if(!$this->helper->isEnabledInFrontend()):
            return [$data];
        endif;

        foreach ($data as $itemId => $itemInfo) {
            $item = $subject->getQuote()->getItemById($itemId);

            $product = $item->getProductId();
            $qty = !empty($itemInfo['qty']) ? $itemInfo['qty'] : 0;
            $minMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->minMessage());
            $maxMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->maxMessage());

            if($item->getProduct()->getType()=='configurable'):
                $product = $item->getOptionByCode('simple_product')->getProduct()->getId();

                if($this->customerSession->isLoggedIn()):
                    $customerId = $this->customerSession->getCustomer()->getId();
                    $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

                else:
                    $customerId = -1;
                    $customerGroupId = 0;
                endif;
                $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

                $restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');
                foreach ($restrictorderquantityCollection as $restrictorderCollection)
                {
                    $groupCheck = false;
                    $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
                    $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
                    $availableProductIds = explode(',',$restrictorderCollection->getProductIds());
                    if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                        $groupCheck = true;

                    }
                    if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {
                        if (in_array($product, $availableProductIds) || $restrictorderCollection->getProductType()==1) {
                            if ($qty < $restrictorderCollection->getMinQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMinQty(), $minMessage)));

                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage)));

                            endif;
                        }
                    }
                }

            else:

                if($this->customerSession->isLoggedIn()):
                    $customerId = $this->customerSession->getCustomer()->getId();
                    $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

                else:
                    $customerId = -1;
                    $customerGroupId = 0;
                endif;
                $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

                $restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');
                foreach ($restrictorderquantityCollection as $restrictorderCollection)
                {
                    $groupCheck = false;
                    $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
                    $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
                    $availableProductIds = explode(',',$restrictorderCollection->getProductIds());
                    if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                        $groupCheck = true;

                    }
                    if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {
                        if (in_array($product, $availableProductIds) || $restrictorderCollection->getProductType()==1) {
                            if ($qty < $restrictorderCollection->getMinQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMinQty(), $minMessage)));

                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage)));

                            endif;
                        }
                    }
                }
            endif;
        }

        return [$data];
    }
    public function beforeUpdateItem($subject, $data, $requestInfo = null, $updatingParams = null)
    {


        if(!$this->helper->isEnabledInFrontend()):
            return [$data,$requestInfo, $updatingParams];
        endif;
        $itemId = $data;
            $item = $subject->getQuote()->getItemById($itemId);

            $product = $item->getProductId();
            $qty = !empty($requestInfo['qty'])?$requestInfo['qty']:1;
            $minMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->minMessage());
            $maxMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->maxMessage());

            if($item->getProduct()->getType()=='configurable'):
                $product = $item->getOptionByCode('simple_product')->getProduct()->getId();

                if($this->customerSession->isLoggedIn()):
                    $customerId = $this->customerSession->getCustomer()->getId();
                    $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

                else:
                    $customerId = -1;
                    $customerGroupId = 0;
                endif;
                $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

                $restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');
                foreach ($restrictorderquantityCollection as $restrictorderCollection)
                {
                    $groupCheck = false;
                    $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
                    $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
                    $availableProductIds = explode(',',$restrictorderCollection->getProductIds());
                    if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                        $groupCheck = true;

                    }
                    if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {
                        if (in_array($product, $availableProductIds) || $restrictorderCollection->getProductType()==1) {
                            if ($qty < $restrictorderCollection->getMinQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMinQty(), $minMessage)));

                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage)));

                            endif;
                        }
                    }
                }

            else:

                if($this->customerSession->isLoggedIn()):
                    $customerId = $this->customerSession->getCustomer()->getId();
                    $customerGroupId = $this->customerSession->getCustomer()->getGroupId();

                else:
                    $customerId = -1;
                    $customerGroupId = 0;
                endif;
                $allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

                $restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');
                foreach ($restrictorderquantityCollection as $restrictorderCollection)
                {
                    $groupCheck = false;
                    $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
                    $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
                    $availableProductIds = explode(',',$restrictorderCollection->getProductIds());
                    if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                        $groupCheck = true;

                    }
                    if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {
                        if (in_array($product, $availableProductIds) || $restrictorderCollection->getProductType()==1) {
                            if ($qty < $restrictorderCollection->getMinQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMinQty(), $minMessage)));

                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                if (strpos($this->redirect->getRefererUrl(), 'customer/section/load') !== false) {
                                    $this->coreSession->start();
                                    $this->coreSession->setCompareRedirect(true);
                                }
                                throw new LocalizedException(__(str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage)));

                            endif;
                        }
                    }
                }
            endif;
        return [$data,$requestInfo, $updatingParams];
    }

}