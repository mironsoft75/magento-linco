<?php

namespace WeltPixel\GoogleTagManager\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class JsCodePosition
 *
 * @package WeltPixel\GoogleTagManager\Model\Config\Source
 */
class JsCodePosition implements ArrayInterface
{

    public const POSITION_HEAD = 'head';
    public const POSITION_BODY = 'body';

    /**
     * Return list of Id Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::POSITION_HEAD,
                'label' => __('In the head tag'),
            ],
            [
                'value' => self::POSITION_BODY,
                'label' => __('Before body close tag'),
            ],
        ];
    }
}