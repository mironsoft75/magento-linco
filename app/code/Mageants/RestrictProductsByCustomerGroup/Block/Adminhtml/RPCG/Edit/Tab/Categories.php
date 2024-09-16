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
use Magento\Config\Model\Config\Source\Yesno;

class Categories extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    /**
     * @var Magento\Cms\Ui\Component\Listing\Column\Cms\Options
     */

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Mageants\Shopbylook\Helper\Option $optionData
     * @param   \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param  \Mageants\Shopbylook\Helper\Discountoption $discountoptionData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mageants\RestrictProductsByCustomerGroup\Helper\Data $helper,
        \Magento\Store\Model\System\Store $systemStore,
        Config $wysiwygConfig,
        Yesno $yesNo,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = array()
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_systemStore = $systemStore;
        $this->_fieldFactory = $fieldFactory;
        $this->_helper = $helper;
        $this->_yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('mageants_rpcg');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Categories')));
        
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id', 'value' => 'id'));
        }
 
        $categoryTreeBlock = $this->getLayout()->createBlock(
            \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class,
            null,
            array('data' => array('js_form_object' => 'сategoryIds'))
        );

        $rpcg_model = $this->_coreRegistry->registry('mageants_rpcg');
        $fieldset->addField(
            'category_ids',
            'hidden',
            array(
                'name' => 'category_ids',
                'data-form-part' => 'edit_form',
                'after_element_js' => $this->getCategoryIdsJs(),
                'value' => $rpcg_model->getCategoryIds()
            )
        );
        $categoryTreeBlock->setCategoryIds(explode(',', $rpcg_model->getCategoryIds()));
        
        $fieldset->addField(
            'show_categories',
            'select',
            array(
                'name' => 'show_categories',
                'label' => __('Include in Navigation Menu'),
                'title' => __('Include in Navigation Menu'),
                'values' => $this->_yesNo->toOptionArray(),
                'note' => 'Include Categories in Navigation Menu in frontend'
            )
        );

        $fieldset->addField(
            'category_tree_container',
            'note',
            array(
                'label' => __('Categories'),
                'title' => __('Categories'),
                'id' => 'category_ids',
                'text' => $categoryTreeBlock->toHtml()
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    private function getCategoryIdsJs()
    {
        return <<<HTML
    <script type="text/javascript">

        сategoryIds = {updateElement : {value : "", linkedValue : ""}};
    
        Object.defineProperty(сategoryIds.updateElement, "value", {
            get: function() {
                return сategoryIds.updateElement.linkedValue
            },
            set: function(v) {
                сategoryIds.updateElement.linkedValue = v;
                jQuery("#page_category_ids").val(v)
            }
        });
    </script>
HTML;
    }
    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Categories');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Categories');
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
