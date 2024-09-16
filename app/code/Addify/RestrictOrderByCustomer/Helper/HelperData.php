<?php

namespace Addify\RestrictOrderByCustomer\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Session\SessionManagerInterface;

class HelperData extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED                        =   'restrictorderbycustomer/general/enabled';
    const XML_PATH_MINMESSAGE                     =   'restrictorderbycustomer/general/minmessage';
    const XML_PATH_MAXMESSAGE                     =   'restrictorderbycustomer/general/maxmessage';




    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer\CollectionFactory $restrictOrderByCustomer
    )
    {
        parent::__construct($context);
        $this->restrictOrderByCustomer = $restrictOrderByCustomer;


    }
    public function isEnabledInFrontend()
    {
        $isEnabled = true;
        $enabled = $this->scopeConfig->getValue(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }
    public function minMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MINMESSAGE, ScopeInterface::SCOPE_STORE);

    }
    public function maxMessage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_MAXMESSAGE, ScopeInterface::SCOPE_STORE);

    }
    public function getCustomers($customerArr)
    {
        $newArray = array();
        $relatedProductsArr = json_decode($customerArr);
        if($relatedProductsArr):
        foreach ($relatedProductsArr as $key => $product)
        {
            $newArray[$key] = $key;
        }

        endif;

        return $relatedProducts  = implode(',', $newArray);

    }
    public function getRelatedProducts($relatedProductsArr)
    {
        $newArray = array();
        $count = 0;
        $relatedProductsArr = json_decode($relatedProductsArr);

        foreach ($relatedProductsArr as $key => $product)
        {
            $newArray[$count] = $key;
            $count++;
        }

        return $relatedProducts  = implode(',', $newArray);

    }
    public function getRelatedProductArray($id) //Return Related Products ID's Array w.r.t Tab ID
    {
        if(isset($id))
        {
            $relatedProducts = $this->restrictOrderByCustomer->create()
                ->addFieldToFilter('restrict_id',$id)->getFirstItem();
            $relatedProducts  = $relatedProducts->getProductIds();
            $productArr = explode(',', $relatedProducts);

            if(!empty($productArr) && $productArr[0] != '')

                return $productArr;
            else
                return '';
        }
    }
    public function getCustomerArray($id) //Return Related Products ID's Array w.r.t Tab ID
    {
        if(isset($id))
        {
            $customer = $this->restrictOrderByCustomer->create()
                ->addFieldToFilter('restrict_id',$id)->getFirstItem();
            $customer  = $customer->getCustomerIds();
            $customerArr = explode(',', $customer);

            if(!empty($customerArr) && $customerArr[0] != '')

                return $customerArr;
            else
                return '';
        }
    }

  
}