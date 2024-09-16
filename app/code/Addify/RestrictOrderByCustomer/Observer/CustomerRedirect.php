<?php
/**
 * Restrict Order Quantity
 *
 * @category Addify
 * @package  Addify_RestrictOrderQuantity
 * @author   Addify
 * @Email    info@addify.com
 */
namespace Addify\RestrictOrderByCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerRedirect implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;


    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    public function __construct(
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->_actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->coreSession = $coreSession;
        $this->catalogSession = $catalogSession;
        $this->request = $request;




    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $this->coreSession->start();
        if ($this->coreSession->getCompareRedirect()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
                $this->coreSession->unsCompareRedirect();
                $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                $this->redirect->redirect($controller->getResponse(), 'catalog/product_compare/index/');
        }

        return $this;
    }
}
