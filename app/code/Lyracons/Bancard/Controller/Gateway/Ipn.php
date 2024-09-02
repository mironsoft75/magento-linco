<?php
namespace Lyracons\Bancard\Controller\Gateway;

use Lyracons\Bancard\Logger\Logger as BancardLogger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Service\InvoiceService;

class Ipn extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
	protected $_context;

	protected $_checkoutSession;

	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * @var BancardLogger
	 */
	protected $logger;

	/**
	 * @var OrderFactory
	 */
	protected $orderFactory;

	/**
	 * @var \Magento\Sales\Model\Order
	 */
	protected $order;

	/**
	 * @var InvoiceService
	 */
	protected $invoiceService;

	/**
	 * @var InvoiceSender
	 */
	protected $invoiceSender;

	/**
	 * @var Transaction
	 */
	protected $transaction;

	/**
	 * @var OrderSender
	 */
	protected $orderSender;

	/**
	 * Ipn constructor.
	 * @param Context $context
	 * @param Session $checkoutSession
	 * @param JsonFactory $resultForwardFactory
	 * @param OrderFactory $orderFactory
	 * @param BancardLogger $logger
	 * @param InvoiceService $invoiceService
	 * @param InvoiceSender $invoiceSender
	 * @param OrderSender $orderSender
	 * @param Transaction $transaction
	 */
	public function __construct(
	    Context $context,
	    Session $checkoutSession,
	    JsonFactory $resultForwardFactory,
	    OrderFactory $orderFactory,
	    BancardLogger $logger,
	    InvoiceService $invoiceService,
	    InvoiceSender $invoiceSender,
	    OrderSender $orderSender,
	    Transaction $transaction
	) {
		$this->_context = $context;
		$this->_checkoutSession = $checkoutSession;
		$this->resultJsonFactory = $resultForwardFactory;
		$this->logger = $logger;
		$this->orderFactory = $orderFactory;

		$this->invoiceService = $invoiceService;
		$this->invoiceSender = $invoiceSender;

		parent::__construct($context);
		$this->transaction = $transaction;
		$this->orderSender = $orderSender;
	}

	/**
	 * Takes the place of the M1 indexAction.
	 * Now, every action has an execute
	 *
	 **/
	public function execute()
	{
		$input = $this->getRequest()->getContent();
		$URLpath = $this->_context->getRequest()->getPathInfo();
		$path_cmd = array_filter(explode('/', $URLpath));
		$this->logger->info("REQUEST: ");
		$this->logger->info(print_r($_REQUEST, true));
		$this->logger->info("IPN: ");
		$this->logger->info(print_r($input, true));

		$this->logger->info('URL');
		$this->logger->info(print_r($path_cmd, true));

		$this->logger->info('SERVER');
		$this->logger->info(print_r($_SERVER, true));

		$debug = $input;
		$debug = $path_cmd;

		if( (is_array($path_cmd)) && (isset($path_cmd[4])) ){

			if($path_cmd[4]=='confirm'){
				// EMITIR PEDIDO DE CONFIRMACION A BANCARD CON $path_cmd[5]
				// getSingleConfirmationUrl()
				// $debug = $this->_helperData;
				$rtn = $this->confirmSingleBuy($path_cmd[5]);
				// var_dump($debug);
				// var_dump($rtn);
				return $this->buildResponse( ['code'=>200, 'input'=> $input ] );
			}

			if($path_cmd[4]=='rollback'){
				// EMITIR PEDIDO DE CONFIRMACION A BANCARD CON $path_cmd[5]
				// getSingleConfirmationUrl()
				// $debug = $this->_helperData;
				$rtn = $this->rollbackSingleBuy($path_cmd[5]);
				// var_dump($debug);
				// var_dump($rtn);
				return $this->buildResponse( ['code'=>200, 'input'=> $input ] );
			}
			
		}

 


		$result = [];

		try {
			$result = $this->decode($input);
			$this->logger->info('decode input $result');
			$this->logger->info(print_r($result, 1));

			$this->loadOrder($result);

			if(!is_null($this->order)){
				$this->saveAdditionalData($result);

				if ($this->isSuccess($result)) {
					$this->invoice();
				} else {
					$this->cancelOrder();
				}
			}

		} catch (\Exception $e) {
			$this->logger->error('Exception on execute: ' . $e->getMessage());
			$result['error'] = $e->getMessage();
			$result['code'] = 400;
		}

		$this->logger->info(print_r($result, 1));

		return $this->buildResponse($result);
	}

	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
	{
		return null;
	}

	public function validateForCsrf(RequestInterface $request): ?bool
	{
		return true;
	}

	private function decode($input)
	{
		$data = json_decode($input);
		$result = [];

		if (!empty($data->operation)) {
			foreach ($data->operation as $key => $value) {
				if (!is_scalar($value)) {
					continue;
				}
				$result[$key] = $value;
			}
		}

		return $result;
	}

	private function saveAdditionalData($result)
	{
		$this->logger->info('Grabando pago');
		$this->logger->info(print_r($result, 1));
		$payment = $this->order->getPayment();

		$payment->setTransactionId(@$result['shop_process_id']);
		$payment->setCcApproval(@$result['result_code']);
		$payment->setCcStatusDescription($this->isSuccess($result) ? 'APROBADA' : 'RECHAZADA');

		/*
		$payment->setLastTransId($authorizationCode);

		#region SPM2IC-350
		$payment->setCcApproval((string)$result->detailOutput->responseCode);
		$payment->setCcStatusDescription((string)$payment->getCcApproval() ? 'RECHAZADA' : 'APROBADA');

		// In case we later want to filter in order grid
		$paymentType = isset($result->detailOutput->paymentTypeCode) ?
		(string)$result->detailOutput->paymentTypeCode : null;

		$payment->setCcType($paymentType);

		$last4 = isset($result->cardDetail) && isset($result->cardDetail->cardNumber) ?
		(string)$result->cardDetail->cardNumber : null;

		$payment->setCcLast4($last4); */

		$additionalInformation = $result;

		$payment->setAdditionalInformation(array_merge($payment->getAdditionalInformation(), $additionalInformation));

		if ($this->order->getCanSendNewEmailFlag()) {
			try {
				$this->orderSender->send($this->order);
				$this->order->save();
			} catch (\Exception $e) {
				$this->logger->critical($e);
			}
		}

	}

	private function invoice()
	{
		/* @var $order Magento\Sales\Model\Order */
		if ($this->order->canInvoice()) {
			$this->logger->info('Pedido facturado');
			$invoice = $this->invoiceService->prepareInvoice($this->order);
			$invoice->register();
			$invoice->save();
			if ($invoice->canCapture()) {
				$invoice->capture();
			} else {
				$invoice->pay();
			}

			$transactionSave = $this->transaction->addObject($invoice)
				->addObject($invoice->getOrder());
			$transactionSave->save();

			$this->invoiceSender->send($invoice);

			//send notification code
			$this->order->addStatusHistoryComment(__('Notified customer about invoice #%1.', $invoice->getId()))
				->setIsCustomerNotified(true)
				->save();
		} else {
			$this->logger->info('No se puede facturar el pedido');
		}
	}

	private function isSuccess(array $result)
	{
		// return true;
		return isset($result['response_code']) && $result['response_code'] == '00';
	}

	/**
	 * @param array $result
	 * @return \Magento\Framework\Controller\Result\Json
	 */
	private function buildResponse(array $result): \Magento\Framework\Controller\Result\Json
	{
		$resultJson = $this->resultJsonFactory->create();

		if (!empty($result['code'])) {
			$resultJson->setHttpResponseCode($result['code']);
			unset($result['code']);
		}

		$response = json_encode($result);
		$resultJson->setData($response);
		return $resultJson;
	}

	private function cancelOrder()
	{
		$this->logger->info('Pedido cancelado');
		$this->order->cancel();
		$this->order->save();
	}

	private function loadOrder(array $result)
	{
		if (empty($result['shop_process_id'])) {
			throw new \Exception("No se informo pedido");
		}
		$orderId = $result['shop_process_id'];

		$this->order = $this->orderFactory->create()->loadByIncrementId($orderId);

		if (empty($this->order->getEntityId())) {
			throw new \Exception(sprintf("Pedido %d no encontrado", $orderId));
		}
	}

	public function confirmSingleBuy($shop_id){  

		// $ch = curl_init($url);
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// curl_setopt(
		//     $ch,
		//     CURLOPT_HTTPHEADER,
		//     [
		//         'Content-Type: application/json',
		//         'Content-Length: ' . strlen($data_string),
		//     ]
		// );

		// $result = curl_exec($ch);
		
	}
	
	public function rollbackSingleBuy($shop_id){  

	}
}
