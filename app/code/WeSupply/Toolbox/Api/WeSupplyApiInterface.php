<?php
namespace WeSupply\Toolbox\Api;


interface WeSupplyApiInterface
{

    /**
     * @param $externalOrderIdString
     * @param bool $ignoreEmailConfirmation
     * @return mixed
     */
    public function weSupplyInterogation($externalOrderIdString, $ignoreEmailConfirmation = false);

    /**
     * @param $orderNo
     * @param $phone
     * @param $prefix
     * @param $country
     * @param $unsubscribe
     * @return mixed
     */
    public function notifyWeSupply($orderNo, $phone, $prefix, $country, $unsubscribe);

    /**
     * @param $ipAddress
     * @param $storeId
     * @param string $zipCode
     * @return mixed
     */
    public function getEstimationsWeSupply($ipAddress, $storeId, $zipCode = '');

    /**
     * @param $protocol
     */
    public function setProtocol($protocol);

    /**
     * @param $apiPath
     * @return mixed
     */
    public function setApiPath($apiPath);

    /**
     * @param $apiClientId
     * @return mixed
     */
    public function setApiClientId($apiClientId);

    /**
     * @param $apiClientSecret
     * @return mixed
     */
    public function setApiClientSecret($apiClientSecret);

    /**
     * @param $params
     * @param bool $multipleProducts
     * @return mixed
     */
    public function getDeliveryEstimations($params, $multipleProducts = false);

    /**
     * @param $endpoint
     * @param $type
     * @param array $params
     * @return mixed
     */
    public function grabUrl($endpoint, $type, $params = []);

    /**
     * @param $ipAddress
     * @param $storeId
     * @param $zipcode
     * @param $countryCode
     * @param $price
     * @param $currency
     * @param $shippers
     * @return mixed
     */
    public function getShipperQuotes($ipAddress, $storeId, $zipcode, $countryCode, $price, $currency, $shippers);


    /**
     * @return mixed
     */
    public function weSupplyAccountCredentialsCheck();

    /**
     * @param $serviceType
     * @return mixed
     */
    public function checkServiceAvailability($serviceType);

    /**
     * @return mixed
     */
    public function getWeSupplyAllowedCountries();
}
