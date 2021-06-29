<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/ 
 */
namespace Solwin\ProductVideo\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('solwin_productvideo_video')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('solwin_productvideo_video')
            )
            ->addColumn(
                'video_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Video ID'
            )
            ->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Video Video Title'
            )
            ->addColumn(
                'video_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Video Choose Video Type'
            )
            ->addColumn(
                'youtube_video_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Youtube Video Video URL'
            )
            ->addColumn(
                'vimeo_video_url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable => false'],
                'Vimeo Video Video URL'
            )
            ->addColumn(
                'video_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Video Upload Video'
            )
            ->addColumn(
                'thumbnail',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Video Video Thumbnail'
            )
            ->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Video Content'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable => false'],
                'Video Status'
            )

            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Video Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Video Updated At'
            )
            ->setComment('Video Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('solwin_productvideo_video'),
                $setup->getIdxName(
                    $installer->getTable('solwin_productvideo_video'),
                    ['title','youtube_video_url','video_file',
                        'thumbnail','content'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title','youtube_video_url','video_file',
                    'thumbnail','content'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        
        /**
         * Create table 'solwin_productvideo_video_store'
         */
        $table = $installer->getConnection()->newTable(
                        $installer->getTable('solwin_productvideo_video_store')
                )->addColumn(
                        'video_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => false, 
                            'primary' => true], 
                        'Video ID'
                )->addColumn(
                        'store_id', 
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 
                        null, 
                        [
                            'unsigned' => true, 
                            'nullable' => false, 
                            'primary' => true
                            ], 
                        'Store ID'
                )->addIndex(
                        $installer->getIdxName(
                                'solwin_productvideo_video_store', ['store_id']
                                ), ['store_id']
                )->addForeignKey(
                        $installer->getFkName(
                                'solwin_productvideo_video_store', 'store_id', 
                                'store', 'store_id'
                                ), 'store_id', 
                        $installer->getTable('store'), 
                        'store_id', 
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                'ProductVideo To Store Linkage Table'
        );
        $installer->getConnection()->createTable($table);
        
        $installer->endSetup();
    }
}