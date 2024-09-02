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

namespace Lyracons\CustomerIdentification\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateCustomerIdentificationAttributeForms implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * AddCustomerUpdatedAtAttribute constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var $customerSetup CustomerSetup * */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($this->getAttributes() as $attribute) {
            $attribute = $customerSetup->getEavConfig()->getAttribute($attribute['entity'], $attribute['code'])
                ->addData($attribute['data']);
            $attribute->save();
        }
    }

    protected function getAttributes()
    {
        return [
            [
                'code' => 'document_number',
                'entity' => Customer::ENTITY,
                'data' => [
                    'is_user_defined' => 1,
                    'is_visible' => 1,
                    'used_in_forms' => [
                        'adminhtml_checkout',
                        'adminhtml_customer',
                        'customer_account_edit',
                        'customer_account_create'
                    ],
                ]
            ],
            [
                'code' => 'document_type',
                'entity' => Customer::ENTITY,
                'data' => [
                    'is_user_defined' => 1,
                    'is_visible' => 1,
                    'used_in_forms' => [
                        'adminhtml_checkout',
                        'adminhtml_customer',
                        'customer_account_edit',
                        'customer_account_create'
                    ],
                    'option' => ['values' => ['DNI', 'CUIT', 'CUIL']],
                ]
            ],
            [
                'code' => 'document_number',
                'entity' => 'customer_address',
                'data' => [
                    'is_user_defined' => 1,
                    'is_visible' => 1,
                    'used_in_forms' => [
                        'adminhtml_customer_address',
                        'customer_address_edit',
                        'customer_register_address',
                    ],
                ]
            ],
            [
                'code' => 'document_type',
                'entity' => 'customer_address',
                'data' => [
                    'is_user_defined' => 1,
                    'is_visible' => 1,
                    'used_in_forms' => [
                        'adminhtml_customer_address',
                        'customer_address_edit',
                        'customer_register_address',
                    ],
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            AddCustomerIdentificationAttribute::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
