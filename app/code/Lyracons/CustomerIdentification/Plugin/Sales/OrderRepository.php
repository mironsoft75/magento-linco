<?php
/**
 * Magento 2 Lyracons CustomerIdentification
 * Copyright (C) 2019  Lyracons
 *
 * This file included in Lyracons/CustomerIdentification is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 * @author Lyracons Dev Team <devteam@lyracons.com>
 */

namespace Lyracons\CustomerIdentification\Plugin\Sales;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

class OrderRepository
{

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /***
     * @param $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     */
    public function afterGetList($subject, $result)
    {
        foreach ($result->getItems() as $order) {
            $this->addExtensionAttributes($order);
        }
        return $result;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    protected function addExtensionAttributes(OrderInterface $order)
    {
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
        return $order;
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