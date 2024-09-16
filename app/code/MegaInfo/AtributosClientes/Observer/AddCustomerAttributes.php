<?php
namespace MegaInfo\AtributosClientes\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddCustomerAttributes implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $order->setCustomerDocumentType($quote->getCustomerDocumentType());
        $order->setCustomerDocumentNumber($quote->getCustomerDocumentNumber());
    }
}

