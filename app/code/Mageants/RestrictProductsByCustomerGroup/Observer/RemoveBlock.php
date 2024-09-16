<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_AskForPrice
 * @copyright Copyright (c) 2017 Mageants
 * @author Mageants <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/*
 * RemoveBlock Observer before render block
 */
class RemoveBlock implements ObserverInterface
{   
    /**
     * current customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Mageants\AskForPrice\Helper\Data
     */
    protected $helperData;

	/**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Mageants\AskForPrice\Helper\Data $helperData
     */	
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->customerSession = $customerSession;
        $this->registry=$registry;
    }

    public function execute(Observer $observer)
    { 
        $layout = $observer->getLayout();
        $block = $layout->getBlock('product.info.addtocart');
        
                      
    }
}