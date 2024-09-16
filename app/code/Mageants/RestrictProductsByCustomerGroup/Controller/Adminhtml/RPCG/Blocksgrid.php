<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Controller\Adminhtml\RPCG;
use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

class Blocksgrid extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) { 
        parent::__construct($context);
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Product for Grid tab
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('rpcg.edit.tab.block')->setInProducts($this->getRequest()->getPost('rpcg_blocks', null));
        return $resultLayout;
    }
}
