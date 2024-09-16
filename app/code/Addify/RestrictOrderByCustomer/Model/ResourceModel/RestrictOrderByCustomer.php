<?php

namespace Addify\RestrictOrderByCustomer\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RestrictOrderByCustomer extends AbstractDb{

    protected function _construct()
    {
        $this->_init('addify_restrictorderquantitybycustomer', 'restrict_id');

    } 

}