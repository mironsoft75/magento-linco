<?php
namespace Lyracons\Slideshow\Block\Adminhtml\Slideshow\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var Action\UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Action\UrlBuilder $actionUrlBuilder
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Context $context, Action\UrlBuilder $actionUrlBuilder, array $data = []
    )
    {
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $data);
    }
}
