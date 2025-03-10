<?php

namespace WeltPixel\GoogleTagManager\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Id
 *
 * @package WeltPixel\GoogleTagManager\Model\Config\Source
 */
class Id implements ArrayInterface
{

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'id',
                'label' => __('ID'),
            ],
            [
                'value' => 'sku',
                'label' => __('SKU'),
            ],
        ];
    }
}