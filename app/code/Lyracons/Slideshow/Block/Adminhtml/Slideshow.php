<?php
namespace Lyracons\Slideshow\Block\Adminhtml;

class Slideshow extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @var \Lyracons\Slideshow\Model\SlideshowFactory
     */
    protected $_slideshowFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context     
     * @param \Lyracons\Slideshow\Model\SlideshowFactory $SlideshowFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Widget\Context $context, \Lyracons\Slideshow\Model\SlideshowFactory $_slideshowFactory, array $data = []
    )
    {
        $this->_slideshowFactory = $_slideshowFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Lyracons\Slideshow\Block\Adminhtml\Slideshow
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_slideshow',
            'label' => __('Add Slideshow'),
            'class' => 'add',
            'button_class' => '',
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Slideshow create url
     *
     * @param string $type
     * @return string
     */
    protected function _getSlideshowCreateUrl()
    {
        return $this->getUrl(
                'slideshow/*/new'
        );
    }
}
