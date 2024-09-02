<?php
// @codingStandardsIgnoreFile
namespace Lyracons\Slideshow\Block\Adminhtml\Slideshow\Helper\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Eav\Model\Entity\Attribute;

class Gallery extends AbstractElement
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\Data\Form\Element\Factory $factoryElement, \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection, \Magento\Framework\Escaper $escaper, \Magento\Framework\View\LayoutInterface $layout, \Magento\Store\Model\StoreManagerInterface $storeManager, $data = []
    )
    {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {

        /* @var $content \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content */
        $content = $this->_layout->createBlock('Lyracons\Slideshow\Block\Adminhtml\Slideshow\Helper\Form\Gallery\Content');
        $content->setValue($this->getData('values'));
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $galleryJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMegiaGallery($galleryJs);
        return $content->toHtml();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * Retrieve data object related with form
     *
     * @return mixed
     */
    public function getDataObject()
    {
        return $this->getForm()->getDataObject();
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return '<tr><td class="value" colspan="3">' . $this->getElementHtml() . '</td></tr>';
    }
}
