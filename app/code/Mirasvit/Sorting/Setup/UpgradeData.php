<?php

namespace Mirasvit\Sorting\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var UpgradeDataInterface[]
     */
    private $pool;

    public function __construct()
    {
        $this->pool = [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
