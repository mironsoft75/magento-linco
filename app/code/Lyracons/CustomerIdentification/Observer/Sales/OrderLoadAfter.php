<?php
/**
 * Created by PhpStorm.
 * User: jgimenez
 * Date: 10/07/2019
 * Time: 22:51
 */

namespace Lyracons\CustomerIdentification\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * OrderLoadAfter constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var $order OrderInterface */
        $order = $observer->getOrder();
        /** @var $extensionAttributes OrderExtensionInterface * */
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->getOrderExtensionDependency();
        }
        $documentType = $order->getData('customer_document_type')
            ? $order->getData('customer_document_type')
            : $order->getBillingAddress()->getData('document_type');
        $documentNumber = $order->getData('customer_document_number')
            ? $order->getData('customer_document_number')
            : $order->getBillingAddress()->getData('document_number');

        $option = $this->getAttributeOption($documentType, (bool)$order->getCustomerIsGuest());
        $extensionAttributes->setCustomerDocumentNumber($documentNumber);
        $extensionAttributes->setCustomerDocumentType($documentType);
        $extensionAttributes->setCustomerDocumentTypeCode($option);

        $order->setExtensionAttributes($extensionAttributes);

        return $this;
    }

    /**
     * @param $option
     * @param bool $isAddress
     * @return bool|string
     */
    protected function getAttributeOption($option, $isAddress = false)
    {
        try {
            $attribute = $this->attributeRepository->get(
                $isAddress ? 'customer_address' : 'customer',
                'document_type');
            if ($attribute->getAttributeId()) {
                return $attribute->getSource()->getOptionText($option);
            }
        } catch (\Exception $exception) {
            $this->logger()->debug($exception->getMessage());
            return false;
        }
        return false;
    }

    /**
     * @return OrderExtensionInterface
     */
    protected function getOrderExtensionDependency()
    {
        $orderExtension = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magento\Sales\Api\Data\OrderExtension'
        );
        return $orderExtension;
    }

}