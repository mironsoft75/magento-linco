<?php

namespace Addify\RestrictOrderByCustomer\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;

class View
{
    private $displayBlocks = ['product.info.addtocart','product.info.addtocart.additional'];

     public function __construct(
        \Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer\CollectionFactory $restrictorderquantityCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Addify\RestrictOrderByCustomer\Helper\HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
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
        $this->sessionFactory = $sessionFactory;

    }

    // you can add layout reference as per you need to display like: product.info.price, product.info.review, etc.
    public function afterToHtml(ProductView $subject, $html)
    {
        // $debug=array('SUBJECT'=>$subject,'THIS'=>$this);
    	$debug=array();
    	// $debug['get_class_methods']=get_class_methods($subject);
    	// $debug['get_object_vars']=get_object_vars($subject);

        if (in_array($subject->getNameInLayout(), $this->displayBlocks)) {
        	$VALIDO = false;
        	$prod=$subject->getProduct();
            $prod_id = $prod->getId();
        	$maxMessage = str_replace('{product_name}',$prod->getName(),$this->helper->maxMessage());


			$customerId = -1;
			$customerGroupId = 0;
            $customerSession = $this->sessionFactory->create();

            if($customerSession->isLoggedIn()){
                $customerId = $customerSession->getCustomer()->getId();
                $customerGroupId = $customerSession->getCustomer()->getGroupId();
            }

			$allGroupId = $this->groupManagement->getAllCustomersGroup()->getId();

        	$restrictorderquantityCollection = $this->restrictorderquantityCollection->create()->addFieldToFIlter('is_active',1)->addStoreFilter($this->storeManager->getStore())->setOrder('priority','asc');

        	foreach ($restrictorderquantityCollection as $restrictorderCollection){
                $groupCheck = false;
                $availableGroup = explode(',',$restrictorderCollection->getCustomerGroup());
                $availableCustomerIds = explode(',',$restrictorderCollection->getCustomerIds());
                $availableProductIds = explode(',',$restrictorderCollection->getProductIds());

                $debug['availableGroup']=$availableGroup;
                $debug['availableCustomerIds']=$availableCustomerIds;
                $debug['availableProductIds']=$availableProductIds;


                if (in_array($customerGroupId, $availableGroup) || in_array($allGroupId, $availableGroup)) {
                    $groupCheck = true;

                }
                if ( in_array($customerId, $availableCustomerIds) || $groupCheck) {
                    if (in_array($prod_id, $availableProductIds) || $restrictorderCollection->getProductType()==1) {
        		          $maxMessage=str_replace('{quantity}', $restrictorderCollection->getMaxQty(), $maxMessage);
                          $VALIDO=true;
                    }
                }
        	}

            $debug['customerId']=$customerId;
            $debug['customerGroupId']=$customerGroupId;
            $debug['allGroupId']=$allGroupId;
            $debug['prod_id']=$prod_id;
            $debug['prod_get_object_vars']=get_object_vars($prod);
            $debug['prod_get_class_methods']=get_class_methods($prod);

            if(!$VALIDO) { return $html; }
            return $html . '<div class="addify_maxmessage">'.$maxMessage.'</div>';
	    	return $html . '<div class="addify_maxmessage">'.$maxMessage.' DEBUG '.(json_encode($debug)).'</div>';
        }

        return $html;
    }
}