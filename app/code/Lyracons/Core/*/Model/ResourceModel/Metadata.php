<?php
/**
 * Created by PhpStorm.
 * User: jgimenez
 * Date: 19/10/2017
 * Time: 14:55
 */

// phpcs:ignoreFile Magento2.Commenting.ClassAndInterfacePHPDocFormatting.InvalidDescription

namespace Lyracons\Core\Model\ResourceModel;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Metadata
 */
class Metadata
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $resourceClassName;

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $resourceClassName
     * @param string $modelClassName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $resourceClassName,
        $modelClassName
    ) {
        $this->objectManager = $objectManager;
        $this->resourceClassName = $resourceClassName;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @return AbstractDb
     */
    public function getMapper()
    {
        return $this->objectManager->get($this->resourceClassName);
    }

    /**
     * @return ExtensibleDataInterface
     */
    public function getNewInstance()
    {
        return $this->objectManager->create($this->modelClassName);
    }
}
