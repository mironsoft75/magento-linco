<?php
namespace Lyracons\Slideshow\Controller\Adminhtml\Slideshow;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lyracons_Slideshow::slideshow');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Lyracons_Slideshow::slideshow');
        $resultPage->addBreadcrumb(__('Lyracons'), __('Lyracons'));
        $resultPage->addBreadcrumb(__('Manage Slideshows'), __('Manage Slideshows'));
        $resultPage->getConfig()->getTitle()->prepend(__('Slideshows'));

        return $resultPage;
    }
}
