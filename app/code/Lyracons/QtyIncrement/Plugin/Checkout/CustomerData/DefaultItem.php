<?php

namespace Lyracons\QtyIncrement\Plugin\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\CustomerData\ItemInterface;

class DefaultItem
{
   /**
     * @var StockRegistryInterface $stockRegistry
     */
    protected $stockRegistry;

    /**
     * DefaultItem constructor.
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @param ItemInterface $defaultItem
     * @param $data
     * @param Item $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemData(ItemInterface $defaultItem, $data, Item $item)
    {
        $data = array_merge($data, ['qty_increment' => $this->getItemQtyIncrements($item)]);

        return $data;

    }

    /**
     * @param Item $item
     * @return false|float|int
     */
    protected function getItemQtyIncrements(Item $item)
    {
        $qtyIncrements = 1;
        $stockItem = $this->stockRegistry->getStockItem($item->getProduct()->getId());

        if ($stockItem->getEnableQtyIncrements() && $stockItem->getQtyIncrements() != 0) {
            $qtyIncrements = $stockItem->getQtyIncrements();
        }
        return $qtyIncrements;
    }
}
