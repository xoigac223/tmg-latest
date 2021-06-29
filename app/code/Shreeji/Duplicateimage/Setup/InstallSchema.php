<?php

namespace Shreeji\Duplicateimage\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()
                ->newTable($installer->getTable('shreeji_duplicateimage'))
                ->addColumn(
                        'manageimage_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true], 'Id'
                )
                ->addColumn(
                        'productname', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Product Name'
                )
                ->addColumn(
                        'filename', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Image Path'
                )
                ->addColumn(
                        'sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Product SKU'
                );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

}
