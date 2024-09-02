<?php

namespace Lyracons\QtyIncrement\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    /**
     * @var StockRegistryInterface $stockRegistry
     */
    protected $stockRegistry;

    /**
     * @param StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * Info constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        $this->stockRegistry = $stockRegistry;
    }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return float|int
     * @internal param \Magento\Catalog\Model\Product $product
     */
    public function getQtyIncrementsByProduct(\Magento\Catalog\Model\Product $product)
    {
        $qtyIncrements = 1;
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        if ($stockItem->getEnableQtyIncrements()
            && $stockItem->getQtyIncrements() != 0) {
            $qtyIncrements = $stockItem->getQtyIncrements();
        }
        return $qtyIncrements;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return float|int
     */
    public function getQtyIncrements(\Magento\Quote\Model\Quote\Item $item)
    {
        $qtyIncrements = 1;
        $stockItem = $this->stockRegistry->getStockItem($item->getProduct()->getId());
        if ($stockItem->getEnableQtyIncrements()
            && $stockItem->getQtyIncrements() != 0) {
            $qtyIncrements = $stockItem->getQtyIncrements();
        }
        return $qtyIncrements;
    }
}