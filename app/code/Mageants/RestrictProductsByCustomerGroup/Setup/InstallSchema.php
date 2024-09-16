<?php
/**
 * @category Mageants RestrictProductsByCustomerGroup
 * @package Mageants_RestrictProductsByCustomerGroup
 * @copyright Copyright (c) 2019 Mageants
 * @author Mageants Team <support@mageants.com>
 */
namespace Mageants\RestrictProductsByCustomerGroup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists('mageants_rpcg')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mageants_rpcg')
            )
             ->addColumn(
                 'id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true),
                 'Id'
             )->addColumn(
                 'rule_name',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Rule Name'
             )->addColumn(
                 'priority',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 array('nullable' => false),
                 'Priority'
             )->addColumn(
                 'start_at',
                 \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                 null,
                 array('default' => '0000-00-00'),
                 'Start At'
             )->addColumn(
                 'end_at',
                 \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                 null,
                 array('default' => '0000-00-00'),
                 'End At'
             )->addColumn(
                 'rpcgstatus',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Rpcg Status'
             )->addColumn(
                 'cgid',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Customer Group'
             )->addColumn(
                 'store_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Show in stores'
             )->addColumn(
                 'response',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Response Type'
             )->addColumn(
                 'errormessage',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Error Message'
             )->addColumn(
                 'redirectoption',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Redirect Option'
             )->addColumn(
                 'productids',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 null,
                 array('nullable' => false),
                 'Product IDs'
             )->addColumn(
                 'url',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 null,
                 array('nullable' => false),
                 'URL Option'
             )->addColumn(
                 'cpid',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'CMS Page'
             )->addColumn(
                 'show_categories',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Include Categories in Navigation Menu'
             )->addColumn(
                 'category_ids',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Category Ids'
             )->addColumn(
                 'blocks',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                 255,
                 array('nullable' => false),
                 'Blocks Ids'
             )->addColumn(
                 'created_at',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                 null,
                 array('nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT),
                 'Created At'
             )->addColumn(
                 'updated_at',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                 null,
                 array('nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE),
                 'Updated At'
             )->setComment(
                 'Mageants Restrict Products By Customer Group'
             );
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
