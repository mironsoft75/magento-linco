<?php

namespace Lyracons\Bancard\Controller\Gateway;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderRepository;

class Redirect extends Action
{
    protected $_context;

    protected $_checkoutSession;

    protected $resultForwardFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ForwardFactory $resultForwardFactory,
        OrderRepository $orderRepository,
        PageFactory $resultPageFactory
    ) {
        $this->_context = $context;
        $this->_checkoutSession = $checkoutSession;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->orderRepository = $orderRepository;
        $this->pageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * Takes the place of the M1 indexAction.
     * Now, every action has an execute
     */
    public function execute()
    {
        $page = $this->pageFactory->create();
        //We are using HTTP headers to control various page caches (varnish, fastly, built-in php cache)
        $page->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

        $order = $this->_checkoutSession->getLastRealOrder();

        if (!empty($order) && $order->getEntityId() > 0) {
            $this->_checkoutSession->setBancardOrderId($order->getEntityId());
            $this->_checkoutSession->restoreQuote();
        } else if ($this->_checkoutSession->getBancardOrderId()) {
            $orderId = $this->_checkoutSession->getBancardOrderId();
            $order = $this->orderRepository->get($orderId);
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
