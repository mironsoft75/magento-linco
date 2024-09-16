<?php

namespace Addify\RestrictOrderByCustomer\Model;

    class RestrictOrderByCustomer extends \Magento\Framework\Model\AbstractModel
    {   
        protected function _construct()
        {
            $this->_init('Addify\RestrictOrderByCustomer\Model\ResourceModel\RestrictOrderByCustomer');
        }
	}