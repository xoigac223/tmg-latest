<?php

namespace Mirasvit\Sorting\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeSchemaInterface[]
     */
    private $pool;

    public function __construct(
        UpgradeSchema\UpgradeSchema101 $upgrade101
    ) {
        $this->pool = [
            '1.0.1'  => $upgrade101
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->pool as $version => $upgrade) {
            if (version_compare($context->getVersion(), $version) < 0) {
                $upgrade->upgrade($setup, $context);
            }
        }

        $setup->endSetup();
    }
}
