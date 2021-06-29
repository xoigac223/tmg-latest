<?php

namespace Themagnet\Orderstatus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
            $installer->getTable('themagnet_orderstatus')
       )->addColumn(
            'orderstatus_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'request_account',
            Table::TYPE_TEXT,
            null,
           ['nullable' => false],
            'Customer Account'
        )->addColumn(
            'request_querytype',
            Table::TYPE_TEXT,
            null,
           ['nullable' => false],
            'Query Type'
        )
        ->addColumn(
            'request_referencenumber',
            Table::TYPE_TEXT,
            null,
           ['nullable' => false],
            'Reference Number'
        )->addColumn(
            'created_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false],
            'created_time'
        )->setComment(
            'Themagnet Orderstatus'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}