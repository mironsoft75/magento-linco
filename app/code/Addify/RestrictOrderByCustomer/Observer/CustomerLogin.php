<?php
/**
 * Restrict Order Quantity
 *
 * @category Addify
 * @package  Addify_RestrictOrderQuantity
 * @author   Addify
 * @Email    info@addify.com
 */
namespace Addify\RestrictOrderByCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;

class CustomerLogin implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;


    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\Session $customerSession,
        \Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer\CollectionFactory $restrictorderquantityCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Addify\RestrictOrderByCustomer\Helper\HelperData $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection,
        \Magento\Checkout\Model\Session $checkoutSession,    
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderfactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->restrictorderquantityCollection = $restrictorderquantityCollection;
        $this->itemCollection = $itemCollection;
        $this->_productCollectionFactory = $productFactory;
        $this->configurable = $configurable;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->_actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->groupManagement = $groupManagement;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->coreSession = $coreSession;
        $this->catalogSession = $catalogSession;
        $this->request = $request;
        $this->orderfactory = $orderfactory;




    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {



        if(!$this->helper->isEnabledInFrontend()):
            return ;
        endif;
        $customer = $observer->getEvent()->getCustomer();
        $quote = $this->checkoutSession->getQuote();
        $controller = $observer->getControllerAction();
        $quoteItem = $this->itemCollection->create()->addFieldToFIlter('quote_id',$quote->getId());


        foreach ($quoteItem as $key => $item) {
            $qty = $item->getQty();                          
            $product = $item->getProductId();
            $minMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->minMessage());
            $maxMessage = str_replace('{product_name}',$item->getProduct()->getName(),$this->helper->maxMessage());

            if($item->getProduct()->getType()=='configurable'):
                $product = $item->getOptionByCode('simple_product')->getProduct()->getId();
                $customer = $observer->getEvent()->getCustomer();
                $customerId = $customer->getId();
                $customerGroupId = $customer->getGroupId();
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

                                $item->delete();
                                $quote->setTriggerRecollect(true)->setTotalsCollectedFlag(false)->collectTotals()->save();
                                
                               
                                return;

                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                $item->delete();
                                $quote->setTriggerRecollect(true)->setTotalsCollectedFlag(false)->collectTotals()->save();
                                
                                return;

                            endif;
                        }
                    }
                }

            else:

                $customer = $observer->getEvent()->getCustomer();
                $customerId = $customer->getId();
                $customerGroupId = $customer->getGroupId();
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
                                $item->delete();
                                $quote->setTriggerRecollect(true)->setTotalsCollectedFlag(false)->collectTotals()->save();
                                
                              
                                return;
                            elseif ($qty > $restrictorderCollection->getMaxQty()):
                                $item->delete();
                                $quote->setTriggerRecollect(true)->setTotalsCollectedFlag(false)->collectTotals()->save();
                                
                               
                                return;

                            endif;
                        }
                    }
                }
            endif;

               
        
        }
      

    }
}

