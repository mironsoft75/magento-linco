<?php
namespace Lyracons\Bancard\Controller\Gateway;

use Magento\Checkout\Controller\Onepage;

class Success extends Onepage
{
    public function execute()
    {
        // Hook point

        // Bancard is sometime informing failures as successes
        // so let's check first
        if ($this->isFailure()) {
            return $this->_redirect('checkout/onepage/failure');
        }

        return $this->_redirect('checkout/onepage/success');
    }

    /**
     * @return bool|\Magento\Framework\Controller\Result\Redirect
     */
    protected function isFailure()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $order = $session->getLastRealOrder();

        return !$order || $order->isCanceled();
    }
}
