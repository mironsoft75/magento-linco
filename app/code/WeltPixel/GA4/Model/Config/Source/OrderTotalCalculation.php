<?php

namespace WeltPixel\GA4\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class OrderTotalCalculation
 *
 * @package WeltPixel\GA4\Model\Config\Source
 */
class OrderTotalCalculation implements ArrayInterface
{

    public const CALCULATE_SUBTOTAL = 'subtotal';
    public const CALCULATE_GRANDTOTAL = 'grandtotal';

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CALCULATE_SUBTOTAL,
                'label' => __('Subtotal'),
            ],
            [
                'value' => self::CALCULATE_GRANDTOTAL,
                'label' => __('Grandtotal'),
            ],
        ];
    }
}
