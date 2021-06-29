<?php

namespace MauroNigrele\SetupTools\Model\Setup;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractInstaller
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configReader;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;
    
    /**
     * @var string
     */
    protected $moduleName;

    /**
     * AbstractInstaller constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $config
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Registry $registry,
        LoggerInterface $logger,
        ScopeConfigInterface $config,
        WriterInterface $configWriter
    ) {
        $this->objectManager = $objectManager;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->configReader = $config;
        $this->configWriter = $configWriter;
    }

    /**
     * @param $path
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    public function getConfig($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->configReader->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * @param $path
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    public function getConfigFlag($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->configReader->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * @param $path
     * @param $value
     * @param string $scope
     * @param int $scopeId
     * @return $this
     */
    public function setConfig($path, $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0)
    {
        $this->configWriter->save($path, $value, $scope, $scopeId);
        return $this;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    public function setModuleDataSetup(ModuleDataSetupInterface $setup)
    {
        $this->moduleDataSetup = $setup;
        return $this;
    }

    /**
     * @return ModuleDataSetupInterface
     */
    public function getModuleDataSetup()
    {
        return $this->moduleDataSetup;
    }
    
    public function setModuleName($name)
    {
        $this->moduleName = $name;
        return $this;
    }
    
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param null|ModuleDataSetupInterface $setup
     * @return \Magento\Eav\Setup\EavSetup
     * @throws LocalizedException
     */
    public function getEavSetup(ModuleDataSetupInterface $setup = null)
    {
        $setup = $setup ?: $this->moduleDataSetup;
        if (!$setup) {
            throw  new LocalizedException(__('Module Setup Resource is not defined. Please execute setModuleSetup() before this.'));
        }
        if (!$this->eavSetup) {
            $this->eavSetup = $this->objectManager->create('\Magento\Eav\Setup\EavSetup', ['setup' => $setup]);
        }
        return $this->eavSetup;
    }
    
    function formattedExport($var, $indent="") {
        switch (gettype($var)) {
            case "string":
//                return '\''. $var . '\'';
//                return "'" . addcslashes($var, "\\\$\"\r\n\t\v\f") . "'";
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case "integer":
            case "int":
            case "float":
                return $var;
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        . ($indexed ? "" : $this->formattedExport($key) . " => ")
                        . $this->formattedExport($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
            case "boolean":
                return $var ? "true" : "false";
            default:
                return var_export($var, true);
        }
    }

}