<?php

namespace MageWorx\SearchSuiteAutocomplete\Model\Source;

class AutocompleteFields
{
    public const SUGGEST = 'suggest';

    public const PRODUCT = 'product';

    /**
     * @var array|array[]
     */
    protected array $options;

    /**
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->options = [
            ['value' => self::SUGGEST, 'label' => __('Suggested')],
            ['value' => self::PRODUCT, 'label' => __('Products')],
        ];

        return $this->options;
    }
}
