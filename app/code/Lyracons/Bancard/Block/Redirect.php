<?php

namespace Lyracons\Bancard\Block;

use Lyracons\Bancard\Logger\Logger as BancardLogger;
use Lyracons\Bancard\Model\Config\Environment;
use Lyracons\Bancard\Model\OrderPayloadGenerator;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;

class Redirect extends Template
{
    public const GUARANIES = 'PYG';

    public const URL_PREFIX_STAGING = "https://vpos.infonet.com.py:8888";
    public const URL_PREFIX_PRODUCTION = "https://vpos.infonet.com.py";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var
     */
    private $processId;

    /**
     * @var OrderPayloadGenerator
     */
    private $payloadGenerator;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        OrderPayloadGenerator $payloadGenerator,
        BancardLogger $logger,
        OrderRepository $orderRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;

        $orderId = $this->checkoutSession->getBancardOrderId();
        if (!empty($orderId)) {
            $this->order = $this->orderRepository->get($orderId);
        }

        parent::__construct($context);
        $this->payloadGenerator = $payloadGenerator;
    }

    /**
     * @return mixed
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return string
     */
    protected function getUrlPrefix()
    {
        if ($this->getEnvironment() == 'staging') {
            return self::URL_PREFIX_STAGING;
        }

        return self::URL_PREFIX_PRODUCTION;
    }

    /**
     * @return string
     */
    public function getJavascriptUrl()
    {
        return sprintf("%s/checkout/javascript/dist/bancard-checkout-1.0.0.js", $this->getUrlPrefix());
    }

    /**
     * @return string
     */
    protected function getInitTransactionurl()
    {
        return sprintf("%s/vpos/api/0.3/single_buy", $this->getUrlPrefix());
    }

    /**
     * @return string|null
     */
    protected function getCachedProcessId()
    {
        return $this->getOrderPayment()->getCCTransId();
    }

    /**
     *
     */
    protected function updatePayment()
    {
        $payment = $this->getOrderPayment();

        $payment->setCcSecureVerify($this->payloadGenerator->getTokenByOrder($this->order));
        $payment->setCCTransId($this->processId);
        $payment->save();
    }

    /**
     * @return string|null
     */
    public function getProcessId()
    {
        /**
         * Sólo se puede usar un shopProcessId una sola vez, por lo que si ya se informó a Bancard,
         * se persiste el id obtenido
         */
        if ($this->getCachedProcessId()) {
            return $this->getCachedProcessId();
        }

        $data_string = json_encode($this->payloadGenerator->getPayload($this->order));
        $url = $this->getInitTransactionUrl();

        $this->_logger->info("getProcessId data");
        $this->_logger->info("$data_string: " . $data_string);
        $this->_logger->info("$url: " . $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string),
            ]
        );

        $result = curl_exec($ch);

        $decoded = json_decode($result);

        $this->logger->info("Respuesta creacion proceso Bancard");
        $this->logger->info($result);

        $this->processId = @$decoded->process_id;

        // Persistimos id obtenido y token
        $this->updatePayment();

        return $this->processId;
    }

    /**
     * @return mixed
     */
    protected function getEnvironment()
    {
        return $this->scopeConfig->getValue(
            Environment::CONFIG_XML_PATH_ENVIRONMENT,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return OrderPaymentInterface|mixed|null
     */
    protected function getOrderPayment()
    {
        return $this->order->getPayment();
    }

    protected function _toHtml()
    {
        if($this->order === null){
            return '';
        }
        return parent::_toHtml();
    }
}
