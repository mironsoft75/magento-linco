<?php

/**
 * Magepow 
 * @category 	Magepow 
 * @copyright 	Copyright (c) 2014 Magepow (http://magepow.com/) 
 * @license 	https://magepow.com/license-agreement.html
 */

namespace Magiccart\Lookbook\Block\Adminhtml\Helper\Grid\Snippet;

class Product extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * LookbookFactory.
     *
     * @var \Magiccart\Lookbook\Model\LookbookFactory
     */
    protected $lookbookFactory;

    /**
     *
     * @param \Magento\Backend\Block\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magiccart\Lookbook\Model\LookbookFactory $lookbookFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->lookbookFactory  = $lookbookFactory;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $storeViewId = $this->getRequest()->getParam('store');
        $item = $this->lookbookFactory->create()->setStoreViewId($storeViewId)->load($row->getId());
        $identifier = $item->getIdentifier();
        $shortcodeWidget = $this->_escaper->escapeHtml('{{widget type="Magiccart\Lookbook\Block\Widget\Product" identifier="' . $identifier . '" template="product.phtml"}}');
        $shortcodeBlock  = $this->_escaper->escapeHtml('<?= $block->getLayout()->createBlock(\'Magiccart\Lookbook\Block\Widget\Product\')->setIdentifier("' . $identifier . '")->setTemplate(\'product.phtml\')->toHtml(); ?>');
        $emojiCopy = '<span style="font-size:30px">✂️</span>';
        $html = '<div class="magiccart-snippet" style="display:inline-block;width:150px; float:left"><input class="copy-input" type="hidden" value="' . $shortcodeWidget . '" readonly><button style="display: inline-flex" class="copy-to-clipboard action-default scalable add primaryx">' . __('Copy to Page|Block') . $emojiCopy . '</button></div>';
        $html .= '<div class="magiccart-snippet" style="display:inline-block;width:150px; float:right"><input class="copy-input" type="hidden" value="' . $shortcodeBlock . '" readonly><button style="display: inline-flex" class="copy-to-clipboard action-default scalable add primaryx">' . __('Copy to .phtml') . $emojiCopy . '</button></div>';

        return $html;
    }
}