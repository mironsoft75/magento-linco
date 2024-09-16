<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */

namespace Mageants\RestrictProductsByCustomerGroup\Block\Adminhtml\RPCG\Edit;

/**
 * Adminhtml RestrictProductsByCustomerGroup edit form block
 *
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context 
     * @param \Magento\Framework\Registry 
     * @param \Magento\Framework\Data\FormFactory
     * @param \Magento\Store\Model\System\Store
     * @param \Mageants\RestrictProductsByCustomerGroup\Helper\Data
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Mageants\RestrictProductsByCustomerGroup\Helper\Data $helper,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_helper = $helper;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('RestrictProductsByCustomerGroup Information'));
    }
    
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            array('data' => array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post', 'enctype' => 'multipart/form-data'))
        );  
       
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
