<?php

namespace Lyracons\Core\Controller\Adminhtml\Entity\Action;

use Lyracons\Core\Controller\Adminhtml\Entity\Action;

/**
 * Generic list action
 */
class Lists extends Action
{

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
