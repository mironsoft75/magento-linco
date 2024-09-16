<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\RestrictProductsByCustomerGroup\Block;
 
class RPCG extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageants\RestrictProductsByCustomerGroup\Model\RPCGFactory $rpcgFactory,
        array $data = array()
    ) {
        $this->_rpcgFactory = $rpcgFactory;
        parent::__construct($context, $data);
    }
    public function getCollection()
    {
        $ruleid =  $this->getRequest()->getParam('id');
        $rulecollection = $this->_rpcgFactory->create()->getCollection();
        foreach ($rulecollection as $value) {
            if ($value['id'] == $ruleid) {
                return $value;
            }
        }    
    }

    public function getPageType()
    {
        return $this->getRequest()->getParam('type');
    }
}