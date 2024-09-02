<?php
namespace Lyracons\Slideshow\Block\Adminhtml\Slideshow\Edit;

/**
 * Theme editor tab container
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Initialize tabs and define tabs block settings
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('slideshow_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Slideshow'));
    }
}
