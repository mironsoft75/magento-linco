<?php

namespace Magepow\Preview\Block\Adminhtml\Category\Tab;

/**
 * Class Product
 */
class Product extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{

    private function disablePreview()
    {
        return !$this->getCategory()->getIsActive()
            || $this->getCategory()->getLevel() < 2;
    }

    /**
     * Clear Cache button
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->disablePreview()) {
            return [];
        }
        $category = $this->getCategory();
        $previewCategory = clone $category;
        $storeId     = (int) $this->getRequest()->getParam('store');
        if ($storeId) {
            $storeUrl   = $previewCategory->getUrlInstance()->getBaseUrl(['_scope' => $storeId]);
            $storeUrl   = explode('/', $storeUrl);
            $previewUrl = $previewCategory->getUrl();
            $previewUrl = explode('/', $previewUrl);
            $previewUrl = array_unique(array_merge($storeUrl, $previewUrl));
            $previewUrl = implode('/', $previewUrl);
        } else {
            $storeId = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getDefaultStoreView()->getId();
            $previewCategory->setStoreId($storeId);
            $previewUrl = $previewCategory->getUrl();
        }

        return [
            'label' => __('Preview'),
            'class' => 'action-secondary preview',
            // 'on_click' => "confirmSetLocation('Are you sure', '{$previewUrl}')",
            'on_click' => 'window.open( \'' . $previewUrl . '\')',
            'sort_order' => 10
        ];
    }

/**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn(
            'Preview',
            [
                'header' => __('Preview'),
                'class' => 'preview',
                'renderer' => 'Magepow\Preview\Block\Adminhtml\Helper\Category\Product\PreviewUrl',
            ]
        );

        return $this;
    }

}