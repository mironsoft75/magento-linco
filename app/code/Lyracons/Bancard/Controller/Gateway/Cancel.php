<?php
namespace Lyracons\Bancard\Controller\Gateway;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Cancel extends Action
{
    public function __construct(
        Context $context,
        Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    public function execute()
    {
        // Hook point
        $this->checkoutSession->restoreQuote();
        return $this->_redirect('checkout/onepage/failure');
    }
}
