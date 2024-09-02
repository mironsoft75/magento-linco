<?php
namespace Lyracons\MegaMenu\Controller\Adminhtml\Index;

use Lyracons\MegaMenu\Controller\Adminhtml\AbstractMassStatus;

class MassEnable extends AbstractMassStatus
{

    public const ID_FIELD = 'menu_id';

    protected $collection = 'Lyracons\MegaMenu\Model\ResourceModel\Megamenu\Collection';
    protected $model = 'Lyracons\MegaMenu\Model\Megamenu';
    protected $status = true;

}
