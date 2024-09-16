<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Block\Adminhtml\RPCG\Edit\Tab;

use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Cms\Ui\Component\Listing\Column\Cms\Options;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mageants\RestrictProductsByCustomerGroup\Helper\Data $helper,
        \Magento\Store\Model\System\Store $systemStore,
        Config $wysiwygConfig,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = array()
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->_fieldFactory = $fieldFactory;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {

          /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('mageants_rpcg');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General')));
        
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => 'id'));
        }

        $fieldset->addField(
            'rule_name',
            'text',
            array('name' => 'rule_name', 'label' => __('Title'), 'title' => __('Title'), 'required' => true)
        );

        $fieldset->addField(
            'priority',
            'text',
            array('name' => 'priority', 'label' => __('Priority'), 'title' => __('Priority'), 'required' => true)
        );

        $fieldset->addField(
            'start_at',
            'date',
            array(
                'name' => 'start_at',
                'label' => __('Start At'),
                'title' => __('Start At'),
                'required' => true,
                'class' => '',
                'singleClick'=> true,
                'date_format' => 'yyyy-MM-dd'
            )
        );

        $fieldset->addField(
            'end_at',
            'date',
            array(
                'name' => 'end_at',
                'label' => __('End At'),
                'title' => __('End At'),
                'required' => true,
                'class' => '',
                'singleClick'=> true,
                'date_format' => 'yyyy-MM-dd'
            )
        );

        $fieldset->addField(
            'rpcgstatus',
            'select',
            array(
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'rpcgstatus',
                'id' => 'rpcgstatus',
                'required' => true,
                'options' => array('Enabled' => __('Enabled'), 'Disabled' => __('Disabled')),
            )
        );

        $cgidarray=$this->_helper->getCgid();
        $fieldset->addField(
            'cgid',
            'multiselect',
            array('name' => 'cgid', 'label' => __('Customer Gruop'), 'title' => __('Customer Name'), 'required' => true,
            'values'=>$cgidarray )
        );
        
        $store_id=$this->_helper->getStoreList();
        $fieldset->addField(
            'store_id',
            'multiselect',
            array(
             'name'     => 'store_id',
             'label'    => __('Store Views'),
             'title'    => __('Store Views'),
             'required' => true,
             'values'   => $store_id,
            )
        );

        $fieldset->addField(
            'response',
            'select',
            array(
                'label' => __('Response'),
                'title' => __('Response'),
                'name' => 'response',
                'id' => 'response',
                'required' => false,
                "values"    =>      array(
                    array("value" => 0,"label" => __("Error Message")),
                    array("value" => 1,"label" => __("Redirect")),
                )
            )
        );

        $fieldset->addField(
            'errormessage',
            'editor',
            array(
                'name' => 'errormessage',
                'label' => __('Error Message'),
                'id' => 'errormessage',
                'title' => __('Error Message'),
                'config' => $this->_wysiwygConfig->getConfig()
            )
        );

        $fieldset->addField(
            'redirectoption',
            'text',
            array('name' => 'redirectoption', 'label' => __('Redirect'), 'title' => __('Redirect'))
        );

        $form->setValues($model->getData());
        /*$form->setUseContainer(true);*/
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * prepare form html
     *
     * @return $string
     */
    public function getFormHtml()
    {
        $html=parent::getFormHtml();
        return $html;
    }
}
