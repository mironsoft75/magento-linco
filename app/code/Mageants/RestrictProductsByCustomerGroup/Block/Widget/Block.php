<?php
namespace Mageants\RestrictProductsByCustomerGroup\Block\Widget;
use Magento\Framework\View\Element\Template;
class Block extends \Magento\Cms\Block\Widget\Block
{

	public function _toHtml()
    {
        $this->setTemplate('Mageants_RestrictProductsByCustomerGroup::widget/static_block/default.phtml');
        return parent::_toHtml();
    }
}