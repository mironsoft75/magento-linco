<?php
namespace WeltPixel\GoogleTagManager\Model\Api;

/**
 * Class \WeltPixel\GoogleTagManager\Model\Api\ConversionTracking
 */
class ConversionTracking extends \WeltPixel\GoogleTagManager\Model\Api
{

    /**
     * Variable names
     */
    public const VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE = 'WP - Conversion Value';
    public const VARIABLE_CONVERSION_TRACKING_ORDER_ID = 'WP - Order ID';

    /**
     * Trigger names
     */
    public const TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE = 'WP - Magento Checkout Success Page';

    /**
     * Tag names
     */
    public const TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING = 'WP - AdWords Conversion Tracking';

    /**
     * Field names used in sending data to dataLayer
     */
    public const FIELD_CONVERSION_TRACKING_CONVERSION_VALUE = 'wp_conversion_value';
    public const FIELD_CONVERSION_TRACKING_ORDER_ID = 'wp_order_id';


    /**
     * @param array $params
     * @return array
     */
    public function createConversionTracking($params)
    {
        $result = [];
        $result = array_merge($result, $this->_createConversionVariables($params));
        $result = array_merge($result, $this->_createConversionTriggers($params));
        $result = array_merge($result, $this->_createConversionTags($params));

        return $result;
    }


    /**
     * @param array $params
     * @return array
     */
    protected function _createConversionVariables($params)
    {
        $accountId = $params['account_id'];
        $containerId = $params['container_id'];
        $existingVariables = $this->_getExistingVariables($accountId, $containerId);
        $result = [];
        $variableFlags = [];

        foreach ($existingVariables as $variable) {
            $variableFlags[$variable['name']] = true;
        }

        $variablesToCreate = $this->_getConversionVariables();

        foreach ($variablesToCreate as $name => $options) {
            /** Ignore already created variables */
            if (isset($variableFlags[$name])) continue;
            try {
                $response = $this->_createVariable($accountId, $containerId, $options);
                if ($response['variableId']) {
                    $result[] = __('Successfully created Conversion Tracking variable: ') . $response['name'];
                } else {
                    $result[] = __('Error creating Conversion Tracking variable: ') . $response['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function _createConversionTriggers($params)
    {
        $accountId = $params['account_id'];
        $containerId = $params['container_id'];
        $existingTriggers = $this->_getExistingTriggers($accountId, $containerId);

        $result = [];
        $triggerFlags = [];

        foreach ($existingTriggers as $trigger) {
            $triggerFlags[$trigger['name']] = true;
        }

        $triggersToCreate = $this->_getConversionTriggers();

        foreach ($triggersToCreate as $name => $options) {
            /** Ignore already created triggers */
            if (isset($triggerFlags[$name])) continue;
            try {
                $response = $this->_createTrigger($accountId, $containerId, $options);
                if ($response['triggerId']) {
                    $result[] = __('Successfully created Conversion Tracking trigger: ') . $response['name'];
                } else {
                    $result[] = __('Error creating Conversion Tracking trigger: ') . $response['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function _createConversionTags($params)
    {
        $accountId = $params['account_id'];
        $containerId = $params['container_id'];
        $existingTags = $this->_getExistingTags($accountId, $containerId);
        $result = [];
        $tagFlags = [];


        foreach ($existingTags as $tag) {
            $tagFlags[$tag['name']] = true;
        }

        $triggersMapping = $this->_getTriggersMapping($accountId, $containerId);
        $tagsToCreate = $this->_getConversionTags($triggersMapping, $params);

        foreach ($tagsToCreate as $name => $options) {
            /** Ignore already created tags */
            if (isset($tagFlags[$name])) continue;
            try {
                $response = $this->_createTag($accountId, $containerId, $options);
                if ($response['tagId']) {
                    $result[] = __('Successfully created Conversion Tracking tag: ') . $response['name'];
                } else {
                    $result[] = __('Error creating Conversion Tracking tag: ') . $response['name'];
                }
            } catch (\Exception $ex) {
                $result[] = $ex->getMessage();
            }
        }

        return $result;
    }

    /**
     * Return list of variables for conversion tracking
     * @return array
     */
    private function _getConversionVariables()
    {
        $variables = 
        [
            self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE => 
            [
                'name' => self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE,
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
                        'value' => self::FIELD_CONVERSION_TRACKING_CONVERSION_VALUE,
                    ],
                ],
            ],
            self::VARIABLE_CONVERSION_TRACKING_ORDER_ID => 
            [
                'name' => self::VARIABLE_CONVERSION_TRACKING_ORDER_ID,
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
                        'value' => self::FIELD_CONVERSION_TRACKING_ORDER_ID,
                    ],
                ],
            ],
        ];

        return $variables;
    }

    /**
     * Return list of triggers for conversion tracking
     * @return array
     */
    private function _getConversionTriggers()
    {
        $triggers = 
        [
            self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE => 
            [
                'name' => self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE,
                'type' => self::TYPE_TRIGGER_PAGEVIEW,
                'filter' => 
                [
                    
                    [
                        'type' => 'contains',
                        'parameter' => 
                        [
                            
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{Page URL}}',
                            ],
                            
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => '/checkout/onepage/success',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $triggers;
    }

    /**
     * Return a list of tags for conversion tracking
     * @param array $triggers
     * @param array $params
     * @return array
     */
    private function _getConversionTags($triggers, $params)
    {
        $tags = 
        [
            self::TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING => 
            [
                'name' => self::TAG_CONVERSION_TRACKING_ADWORDS_CONVERSION_TRACKING,
                'firingTriggerId' => 
                [
                    $triggers[self::TRIGGER_CONVERSION_TRACKING_MAGENTO_CHECKOUT_SUCCESS_PAGE],
                ],
                'type' => self::TYPE_TAG_AWCT,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => 
                [
                    
                    [
                        'type' => 'boolean',
                        'key' => 'enableConversionLinker',
                        'value' => "true",
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionValue',
                        'value' => '{{' . self::VARIABLE_CONVERSION_TRACKING_CONVERSION_VALUE . '}}',
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'orderId',
                        'value' => '{{' . self::VARIABLE_CONVERSION_TRACKING_ORDER_ID . '}}',
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionId',
                        'value' => $params['conversion_id'],
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'currencyCode',
                        'value' => $params['conversion_currency_code'],
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionLabel',
                        'value' => $params['conversion_label'],
                    ],
                    
                    [
                        'type' => 'template',
                        'key' => 'conversionCookiePrefix',
                        'value' => '_gcl',
                    ],
                ],
            ],
        ];

        return $tags;
    }

    /**
     * @return array
     */
    public function getConversionVariablesList()
    {
        return $this->_getConversionVariables();
    }

    /**
     * @return array
     */
    public function getConversionTriggersList()
    {
        return $this->_getConversionTriggers();
    }

    /**
     * @param array $triggers
     * @param array $params
     * @return array
     */
    public function getConversionTagsList($triggers, $params)
    {
        return $this->_getConversionTags($triggers, $params);
    }
}
