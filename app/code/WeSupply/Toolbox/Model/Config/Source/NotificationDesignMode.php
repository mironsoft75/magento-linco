<?php
namespace WeSupply\Toolbox\Model\Config\Source;

class NotificationDesignMode implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * notification first design code
     */
    public const FIRST_DESIGN_CODE = 'first_design';

    /**
     * notification first design label
     */
    public const FIRST_DESIGN_LABEL ='Design 1';

    /**
     * notification second design code
     */
    public const SECOND_DESIGN_CODE = 'second_design';

    /**
     * notification second design label
     */
    public const  SECOND_DESIGN_LABEL = 'Design 2';

    public function toOptionArray()
    {
        return [
            ['value' => self::FIRST_DESIGN_CODE, 'label' => __(self::FIRST_DESIGN_LABEL)],
            ['value' => self::SECOND_DESIGN_CODE, 'label' => __(self::SECOND_DESIGN_LABEL)],
        ];
    }
}