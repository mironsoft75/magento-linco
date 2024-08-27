<?php

namespace Magepow\Preview\Block\Adminhtml\Helper\Category\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;

class PreviewUrl extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * [__construct description].
     *
     * @param \Magento\Backend\Block\Context $context
     * @param StoreManagerInterface          $storeManager
     * @param ProductRepositoryInterface     $productRepository
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->productRepository  = $productRepository;
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
        $storeId = $this->getRequest()->getParam('store');
        $productId = $row->getId();  
        if ($this->disablePreview($row)) {
            return '';
        }
        if (!$storeId) {
            // $storeId = $this->storeManager->getStore()->getId();
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $product   = $this->productRepository->getById($productId, false, $storeId);
        $productURL = $product->setStoreId($storeId)->getUrlModel()->getUrlInStore($product, ['_escape' => true]);
        return html_entity_decode('<a target="_blank" class="product_preview_' . $productId . '" href="' . $productURL . '">' . __('Preview') . '</a><script>document.querySelector(".product_preview_' . $productId . '").addEventListener("click", function (e) {e.stopPropagation();});</script>');
    }

    private function disablePreview($item)
    {
        return $item['status'] == Status::STATUS_DISABLED
            || $item['visibility'] == Visibility::VISIBILITY_NOT_VISIBLE;
    }

}
