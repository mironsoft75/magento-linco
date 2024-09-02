<?php

namespace Lyracons\Bancard\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    public const CONFIG_XML_PATH_ENVIRONMENT = 'payment/lyracons_bancard/environment';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            'staging' => 'Staging',
            'production' => 'Producción',
        ];
    }
}
