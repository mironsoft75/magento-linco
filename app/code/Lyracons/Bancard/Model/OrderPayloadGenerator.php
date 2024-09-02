<?php

namespace Lyracons\Bancard\Model;

use Lyracons\Bancard\Logger\Logger as BancardLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

class OrderPayloadGenerator
{
    public const GUARANIES = 'PYG';
    public const CONFIG_XML_PATH_PUBLIC_KEY = 'payment/lyracons_bancard/public_key';
    public const CONFIG_XML_PATH_PRIVATE_KEY = 'payment/lyracons_bancard/private_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BancardLogger
     */
    private $logger;

    /**
     * OrderPayloadGenerator constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BancardLogger $logger,
        UrlInterface $urlBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
    }

    public function getTokenByOrder(Order $order, $currency = self::GUARANIES)
    {
        $shopProcessId = $this->extractProcessId($order);
        $total = $this->extractTotal($order);

        return $this->getToken($shopProcessId, $total, $currency);
    }

    public function getToken($shopProcessId, $total, $currency)
    {
        $hash = $this->getPrivateKey() . $shopProcessId . $this->formatedTotal($total) . $currency;
        $md5 = md5($hash);
        $this->logger->info('getToken', [
            'hash' => $hash,
            'priv' => $this->getPrivateKey(),
            'proc' => $shopProcessId,
            'tot' => $this->formatedTotal($total),
            'cur' => $currency,
            'md5' => $md5,
        ]);

        return $md5;
    }

    /**
     * @param $total
     * @return string
     */
    protected function formatedTotal($total)
    {
        return number_format($total, 2, '.', '');
    }

    /**
     * @return mixed
     */
    public function getPublicKey()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PUBLIC_KEY,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_PATH_PRIVATE_KEY,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return mixed
     */
    public function getSuccessUrl()
    {
        return $this->urlBuilder->getUrl('bancard/gateway/success');
    }

    /**
     * @return mixed
     */
    public function getCancelUrl()
    {
        return $this->urlBuilder->getUrl('bancard/gateway/cancel');
    }

    /**
     * @param Order $order
     * @param string $targetCurrency
     * @return array
     */
    public function getPayload(Order $order, $targetCurrency = self::GUARANIES)
    {
        $shopProcessId = $this->extractProcessId($order);
        $total = $this->extractTotal($order);

        return $this->getPayloadImp($shopProcessId, $total, $targetCurrency);
    }

    /**
     * @param string $shopProcessId
     * @param float $total
     * @param $targetCurrency
     * @return array
     */
    public function getPayloadImp(string $shopProcessId, float $total, $targetCurrency): array
    {
        return [
            "public_key" => $this->getPublicKey(),
            "operation" => [
                "token" => $this->getToken($shopProcessId, $total, $targetCurrency),
                "shop_process_id" => $shopProcessId,
                "currency" => $targetCurrency,
                "amount" => $this->formatedTotal($total),
                "additional_data" => '',
                "description" => '-',
                "return_url" => $this->getSuccessUrl(),
                "cancel_url" => $this->getCancelUrl(),
            ],
        ];
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function extractProcessId(Order $order): string
    {
        $shopProcessId = $order->getIncrementId();
        return (int) $shopProcessId;
    }

    /**
     * @param Order $order
     * @return float
     */
    protected function extractTotal(Order $order): float
    {
        $total = $order->getGrandTotal();
        return $total;
    }
}
