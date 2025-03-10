<?php

namespace WeltPixel\GA4\Model;

/**
 * Class \WeltPixel\GA4\Model\Dimension
 */
class Dimension extends \WeltPixel\GA4\Model\Storage
{
    public const DIMENSION_TYPE = 'dimension';
    public const METRIC_TYPE = 'metric';
    public const DIMENSION_STOCK_STATUS = 'item_stock_status';
    public const DIMENSION_SALE_PRODUCT = 'item_sale_product';
    public const DIMENSION_REVIEWS_COUNT = 'item_reviews_count';
    public const DIMENSION_REVIEWS_SCORE = 'item_reviews_score';

    /**
     * @var \Magento\Review\Model\Review\SummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\Review\SummaryFactory $reviewSummaryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        parent::__construct($context, $registry);
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param $product
     * @return \Magento\Review\Model\Review\Summary
     */
    public function getReviewSummary($product)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $reviewSummary = $this->reviewSummaryFactory->create();
        $reviewSummary->setData('store_id', $storeId);
        $summaryModel = $reviewSummary->load($product->getId());

        return $summaryModel;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \WeltPixel\GA4\Helper\Data $gtmHelper
     * @return array
     */
    public function getProductDimensions($product, $gtmHelper)
    {
        $dimensions = [];

        if ($gtmHelper->trackStockStatusEnabled()) {
            $productStockStatus = ($product->isAvailable()) ? 'In stock' : 'Out of stock';
            $dimensions[self::DIMENSION_STOCK_STATUS] = $productStockStatus;
        }

        if ($gtmHelper->trackSaleProductEnabled()) {
            $saleProduct = ($product->getSale()) ? 'Yes' : 'No';
            $dimensions[self::DIMENSION_SALE_PRODUCT] = $saleProduct;
        }

        if ($this->moduleManager->isEnabled('Magento_Review')) {
            $summaryModel = $this->getReviewSummary($product);

            if ($gtmHelper->trackReviewsCountEnabled()) {
                $reviewCount = ($summaryModel->getReviewsCount()) ? $summaryModel->getReviewsCount() : 0;
                $dimensions[self::DIMENSION_REVIEWS_COUNT] = strval($reviewCount);
            }

            if ($gtmHelper->trackReviewsScoreEnabled()) {
                $ratingSummary = $summaryModel->getRatingSummary();
                $dimensions[self::DIMENSION_REVIEWS_SCORE] = strval($ratingSummary / 20);
            }
        }

        for ($i=1; $i<=5; $i++) {
            if ($gtmHelper->trackCustomAttribute($i)) {
                $attributeValue = $gtmHelper->getCustomAttributeValue($i, $product);
                $attributeCode =  $gtmHelper->getCustomAttributeName($i);
                $dimensions[$attributeCode] = $attributeValue;
            }
        }

        return $dimensions;
    }
}
