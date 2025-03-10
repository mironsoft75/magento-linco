<?php

namespace WeltPixel\GA4\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ParentVsChild
 *
 * @package WeltPixel\GA4\Model\Config\Source
 */
class ParentVsChild implements ArrayInterface
{

    public const CHILD = 'child';
    public const PARENT = 'parent';

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CHILD,
                'label' => __('Child'),
            ],
            [
                'value' => self::PARENT,
                'label' => __('Parent'),
            ],
        ];
    }
}
