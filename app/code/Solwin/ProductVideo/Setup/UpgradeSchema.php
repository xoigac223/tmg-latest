<?php

namespace Solwin\ProductVideo\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{

    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {

        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'catalog_product_entity'
         */

        if ($installer->tableExists('solwin_productvideo_video')) {

            if (!$installer->getConnection()
               ->tableColumnExists(
                       'solwin_productvideo_video',
                       'products'
                       )
               ) {
           $installer->getConnection()->addColumn(
                   $installer->getTable('solwin_productvideo_video'),
                   'products',
                       [
                           'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                           'nullable' => true,
                           'comment' => 'Products'
                       ]
                   );
            }

        }
        $installer->endSetup();
    }

}
