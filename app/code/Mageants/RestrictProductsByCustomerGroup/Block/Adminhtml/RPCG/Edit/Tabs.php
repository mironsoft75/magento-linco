<?php
/**
  * @category   Mageants RestrictProductsByCustomerGroup
  * @package    Mageants_RestrictProductsByCustomerGroup
  * @copyright  Copyright (c) 2019 Mageants
  * @author     Mageants Team <support@Mageants.com>
  */
namespace Mageants\RestrictProductsByCustomerGroup\Block\Adminhtml\RPCG\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('checkmodule_rpcg_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule Information'));
    }
}