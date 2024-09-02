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

class AddCustomerIdentificationAttribute implements DataPatchInterface, PatchVersionInterface
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
            $customerSetup->addAttribute(
                $attribute['entity'],
                $attribute['code'],
                $attribute['data']
            );
        }
    }

    protected function getAttributes()
    {
        return [
            [
                'code' => 'document_type',
                'entity' => Customer::ENTITY,
                'data' => [
                    'type' => 'int',
                    'label' => 'Document Type',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'required' => true,
                    'sort_order' => 50,
                    'visible' => true,
                    'system' => false,
                    'validate_rules' => '[]',
                    'position' => 50,
                    'admin_checkout' => 1,
                    'is_used_in_grid' => 1,
                    'option' => ['values' => ['DNI', 'CUIT', 'CUIL']],
                ]
            ],
            [
                'code' => 'document_number',
                'entity' => Customer::ENTITY,
                'data' => [
                    'type' => 'varchar',
                    'label' => ' Document Number',
                    'input' => 'text',
                    'required' => true,
                    'sort_order' => 51,
                    'position' => 55,
                    'visible' => true,
                    'system' => false,
                    'admin_checkout' => 1,
                    'validate_rules' => '{"input_validation":"numeric","max_text_length":11,"min_text_length":8}',
                ]
            ],
            [
                'code' => 'document_type',
                'entity' => 'customer_address',
                'data' => [
                    'type' => 'int',
                    'label' => 'Document Type',
                    'input' => 'select',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'required' => true,
                    'sort_order' => 50,
                    'visible' => true,
                    'system' => false,
                    'validate_rules' => '[]',
                    'position' => 50,
                    'admin_checkout' => 1,
                    'is_used_in_grid' => 1,
                    'option' => ['values' => ['DNI', 'CUIT', 'CUIL']],
                ]
            ],
            [
                'code' => 'document_number',
                'entity' => 'customer_address',
                'data' => [
                    'type' => 'varchar',
                    'label' => ' Document Number',
                    'input' => 'text',
                    'required' => true,
                    'sort_order' => 51,
                    'position' => 55,
                    'visible' => true,
                    'system' => false,
                    'admin_checkout' => 1,
                    'validate_rules' => '{"input_validation":"numeric","max_text_length":11,"min_text_length":8}',
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            ///UpdateIdentifierCustomerAttributesVisibility::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
