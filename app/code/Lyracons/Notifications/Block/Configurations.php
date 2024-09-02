<?php
namespace Lyracons\Notifications\Block;

class Configurations extends \Magento\Framework\View\Element\Template
{
    protected $_helper;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Lyracons\Notifications\Helper\NotificationsHelper $_helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Lyracons\Notifications\Helper\NotificationsHelper $_helper
    )
    {
        parent::__construct($context);
        $this->_helper = $_helper;
    }

    public function getConfigData(){
        $_configData = $this->_helper->getConfigValues();

        return $_configData;
    }
}