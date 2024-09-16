<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Block\Adminhtml\RPCG\Edit\Tab;

class CmsBlock extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Current productCollectionFactory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;
    
    /**
     * Product Model Type
     *
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;
    
    /**
     * product attribute status
     *
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    
    /**
     * Model product Visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;
    
    /**
     * @param \Magento\Backend\Block\Template\Context 
     * @param \Magento\Backend\Helper\Data
     * @param \Magento\Framework\ObjectManagerInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     * @param \Magento\Catalog\Model\Product\Type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status
     * @param \Magento\Catalog\Model\Product\Visibility
     * @param array
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockColFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Mageants\RestrictProductsByCustomerGroup\Model\RPCGFactory $rpcgFactory,
        array $data = array()
    ) { 
        
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->blockColFactory = $blockColFactory;
        $this->_visibility = $visibility;
        $this->_rpcgFactory = $rpcgFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    
    /**
     * Prepare construct
     *
     */ 
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blocksGrid');
        $this->setDefaultSort('block_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_blocks') {
            $BlockIds = $this->getSelectedBlocks();

            if (empty($BlockIds)) {
                $BlockIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('block_id', array('in' => $BlockIds));
            } else {
                if ($BlockIds) {
                    $this->getCollection()->addFieldToFilter('block_id', array('nin' => $BlockIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
    /**
     * Prepare Collection
     *
     * @return _parentCollection
     */ 
    protected function _prepareCollection()
    {
        $blockscollections = $this->blockColFactory->create();
        $this->setCollection($blockscollections);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
       
        $this->addColumn(
            'in_blocks',
            array(
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_blocks',
                'align' => 'center',
                'index' => 'block_id',
                'values' => $this->getSelectedBlocks(),
            )
        );

        $this->addColumn(
            'block_id',
            array(
                'header' => __('Block ID'),
                'type' => 'number',
                'index' => 'block_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'width' => '10px',
            )
        );

        $this->addColumn(
            'title',
            array(
                'header' => __('Name'),
                'index' => 'title',
                'class' => 'xxx',
                'width' => '50px',
            )
        );

        $this->addColumn(
            'identifier',
            array(
                'header' => __('Identifier'),
                'index' => 'identifier',
                'class' => 'xxx',
                'width' => '50px',
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('rpcg/rpcg/blocksgrid', array('_current' => true));
    }

    /**
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '';
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
        return true;
    }

    public function getSelectedBlocks()
    {   
        $rule_id = $this->getRequest()->getParam('id');
        $collection = $this->_rpcgFactory->create()->load($rule_id);
        $blocks = explode(",", $collection['blocks']);
        return $blocks;
    }

}
