<?php
namespace WeltPixel\GA4\Model\Api;

/**
 * Class \WeltPixel\GA4\Model\Api\Remarketing
 */
class Remarketing extends \WeltPixel\GA4\Model\Api
{
    /**
     * Variable names
     */
    public const VARIABLE_REMARKETING_GOOGLE_TAG = 'WP - Google Tag Params';

    /**
     * Tag names
     */
    public const TAG_REMARKETING_ADWORDS_REMARKETING = 'WP - AdWords Remarketing';

    /**
     * Field names used in sending data to dataLayer
     */
    public const FIELD_REMARKEING_GOOGLE_TAG = 'google_tag_params';


    public const ECOMM_PAGETYPE_HOME = 'home';
    public const ECOMM_PAGETYPE_CATEGORY = 'category';
    public const ECOMM_PAGETYPE_SEARCHRESULTS = 'searchresults';
    public const ECOMM_PAGETYPE_PRODUCT = 'product';
    public const ECOMM_PAGETYPE_CART = 'cart';
    public const ECOMM_PAGETYPE_PURCHASE = 'purchase';
    public const ECOMM_PAGETYPE_CHECKOUT = 'checkout';
    public const ECOMM_PAGETYPE_OTHER = 'other';

    /**
     * Return list of variables for remarketing
     * @return array
     */
    private function _getRemarketingVariables()
    {
        $variables = 
        [
            self::VARIABLE_REMARKETING_GOOGLE_TAG => 
            [
                'name' => self::VARIABLE_REMARKETING_GOOGLE_TAG,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => 
                [
                    
                    [
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2",
                    ],
                    
                    [
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false",
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'name',
                        'value' => self::FIELD_REMARKEING_GOOGLE_TAG,
                    ],
                ],
            ],
        ];

        return $variables;
    }

    /**
     * Return a list of tags for remarketing
     * @param array $triggers
     * @param array $params
     * @return array
     */
    private function _getRemarketingTags($triggers, $params)
    {
        $tags = 
        [
            self::TAG_REMARKETING_ADWORDS_REMARKETING => 
            [
                'name' => self::TAG_REMARKETING_ADWORDS_REMARKETING,
                'firingTriggerId' => 
                [
                    self::TRIGGER_ALL_PAGES_ID,
                ],
                'type' => self::TYPE_TAG_SP,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => 
                [
                    
                    [
                        'type' => 'template',
                        'key' => 'dataLayerVariable',
                        'value' => '{{' . self::VARIABLE_REMARKETING_GOOGLE_TAG . '}}',
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'customParamsFormat',
                        'value' => 'DATA_LAYER',
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionId',
                        'value' => $params['conversion_code'],
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionLabel',
                        'value' => $params['conversion_label'],
                    ],
                ],
            ],
        ];

        return $tags;
    }

    /**
     * @return array
     */
    public function getRemarketingVariablesList()
    {
        return $this->_getRemarketingVariables();
    }

    /**
     * @param array $triggers
     * @param array $params
     * @return array
     */
    public function getRemarketingTagsList($triggers, $params)
    {
        return $this->_getRemarketingTags($triggers, $params);
    }
}
