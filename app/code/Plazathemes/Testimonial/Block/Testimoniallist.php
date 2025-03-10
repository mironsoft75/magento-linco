<?php
/**
* Copyright © 2015 PlazaThemes.com. All rights reserved.

* @author PlazaThemes Team <contact@plazathemes.com>
*/

namespace Plazathemes\Testimonial\Block;

use Magento\Framework\UrlInterface;

/**
 * Main contact form block
 */
class Testimoniallist extends \Magento\Framework\View\Element\Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    protected $_testimonialFactory;
	protected $customerSession;
	/**
     * url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Plazathemes\Testimonial\Model\TestimoFactory $testimonialFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) 
    {
        $this->_testimonialFactory = $testimonialFactory;
		$this->customerSession = $customerSession;
        parent::__construct($context, $data);
        //get collection of data 
        $collection = $this->_testimonialFactory->create()->getCollection();
		$collection->addFieldToFilter('status', 1);
		$collection->setOrderByTestimo();
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('Testimonials'));
    }
	
	public function sidebarCollection(){
		$collection = $this->_testimonialFactory->create()->getCollection();
		$collection->addFieldToFilter('status', 1);
		$collection->setOrderByTestimo();
		return $collection;
	}

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection 
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'plazathemes.testimonial.record.pager'
            );
			$pager->setAvailableLimit([$this->getConfig('per_page')=>$this->getConfig('per_page')]);
			$pager->setCollection(
			    $this->getCollection() // assign collection to pager
			);
            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }
  
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    } 

    public function getBaseUrl()
    {
    return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
    }
	
	public function getMediaUrl()
    {
		
		 return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).'/testimonial/image';
    }
	
	public function getMediabaseUrl()
    {
		$media_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $media_folder;
    }
	public function getcustomerSession()
    {
        if ($this->customerSession->isLoggedIn()) {
			
           return $this->customerSession->getCustomer()->getData();
        }
    }
	
	public function checklogin()
	{
		return $this->customerSession->isLoggedIn();
	}
	
	public function getDefaultImage()
    {
        return $this->getViewFileUrl('Plazathemes_testimonial::images/default.jpg');
    }
	
	
	public function getConfig($config)
	{
		return $this->_scopeConfig->getValue('testimonial/general/'.$config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
}
