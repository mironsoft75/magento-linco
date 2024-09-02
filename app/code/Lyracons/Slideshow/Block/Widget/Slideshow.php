<?php
// @codingStandardsIgnoreFile
namespace Lyracons\Slideshow\Block\Widget;

/**
 * Lyracons slideshow content 
 */
class Slideshow extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Slideshow factory
     *
     * @var \Lyracons\Slideshow\Model\SlideshowFactory
     */
    protected $_slideshowFactory;
    protected $_objectSlideshow;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context $context    
     * @param \Lyracons\Slideshow\Model\BlockFactory $blockFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Lyracons\Slideshow\Model\SlideshowFactory $slideshowFactory, \Magento\Framework\App\Http\Context $httpContext, array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_slideshowFactory = $slideshowFactory;
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['LYRACONS_SLIDESHOW',
            ],]);
    }

    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function getSlideshow()
    {
        $slideshowId = $this->getSlideshowId();

        if ($slideshowId) {
            $slideshow = $this->_slideshowFactory->create();
            $slideshow->load($slideshowId, 'identifier');
            if ($slideshow->isActive()) {
                return $slideshow;
            }
        }
    }

    protected function _beforeToHtml()
    {
        $this->setSlideshowData($this->getSlideshow());
        return parent::_beforeToHtml();
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $slideshow = serialize($this->getData());

        return [
            'LYRACONS_SLIDESHOW',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $slideshow
        ];
    }

    public function getTemplate()
    {
        $template = "slideshow.phtml";
        return $template;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Lyracons\Slideshow\Model\Block::CACHE_TAG . '_' . $this->getSlideshowId()];
    }
}
