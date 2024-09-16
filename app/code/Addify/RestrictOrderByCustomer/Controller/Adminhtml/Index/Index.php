<?php

namespace Addify\RestrictOrderByCustomer\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) 
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Addify_RestrictOrderByCustomer::managerestrictorderbycustomer');
        $resultPage->addBreadcrumb(__('Addify'), __('Addify'));
        $resultPage->addBreadcrumb(__('Manage Order Quantity By Customer'), __('Manage Order Quantity By Customer'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Order Quantity By Customer'));
        return $resultPage;
    }


 
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Addify_RestrictOrderByCustomer::managerestrictorderbycustomer');

    }
}