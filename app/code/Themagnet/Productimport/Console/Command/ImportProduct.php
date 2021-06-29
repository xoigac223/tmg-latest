<?php
namespace Themagnet\Productimport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Framework\Registry;;
use Magento\Framework\ObjectManagerInterface;

class ImportProduct extends Command
{
    /**
     * Customer argument
     */
    const SIMPLE_ARGUMENT = '-s';
    const CONFIG_ARGUMENT = '-c';
    const PRODUCT_ATTRIBUTE = '-a';
    const PRODUCT_PRICE = '-p';
    const IMPORT_PRICE = '-r';
    const POST_IMPORT = '-t';
    const POST_PRODUCT_IMAGE = '-i';
    const POST_PRODUCT_COLOR = '-l';
    /**
     * Allow all
     */
    const ALLOW_SIMPLE = 'allow-simple';
    const ALLOW_CONFIG = 'allow-config';
    const ALLOW_ATTRIBUTE = 'import-attribute';
    const ALLOW_PRICE = 'import-price';
    const ALLOW_RESET = 'import-restart';
    const FILE_TRANSFER = 'post-import';
    const PRODUCT_IMAGE = 'import-image';
    const ALLOW_COLOR = 'color';
 
 
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $objectManager;
    protected $customCommand;
    protected $importproduct;
    protected $_csvfiles;
    
 

    public function __construct(
        Registry $registry,
        /*\Themagnet\Productimport\Model\Importproduct $importproduct,
        \Themagnet\Productimport\Model\Csvfiles $csvfiles,*/
        ObjectManagerInterface $objectManager
    )
    {
        $this->registry = $registry;
        //$this->importproduct = $importproduct;
        $this->objectManager = $objectManager;
        //$this->_csvfiles = $csvfiles;
        $this->customCommand = new ThemagnetCommand();
        parent::__construct();
    }

    protected function configure()
    {

        $this->setName('themagnet:importProduct')
            ->setDescription('For Simple product option -s|--allow-simple , Config product -c|--allow-config , Import attributs -a|--import-attribute, Import Advanced Pricing -p|--import-price , Image import -i|import-image, Image import -l|color')
            ->setDefinition([
                new InputOption(
                    self::ALLOW_SIMPLE,
                    self::SIMPLE_ARGUMENT,
                    InputOption::VALUE_NONE,
                    'Allow Simple Product'
                ),
                new InputOption(
                    self::ALLOW_CONFIG,
                    self::CONFIG_ARGUMENT,
                    InputOption::VALUE_NONE,
                    'Allow Configurable Product'
                ),
                new InputOption(
                    self::ALLOW_ATTRIBUTE,
                    self::PRODUCT_ATTRIBUTE,
                    InputOption::VALUE_NONE,
                    'Import product attributs'
                ),
                new InputOption(
                    self::ALLOW_PRICE,
                    self::PRODUCT_PRICE,
                    InputOption::VALUE_NONE,
                    'Import product price'
                ),
                new InputOption(
                    self::ALLOW_RESET,
                    self::IMPORT_PRICE,
                    InputOption::VALUE_NONE,
                    'Import process restart'
                ),
                new InputOption(
                    self::FILE_TRANSFER,
                    self::POST_IMPORT,
                    InputOption::VALUE_NONE,
                    'Post import process'
                ),
                new InputOption(
                    self::PRODUCT_IMAGE,
                    self::POST_PRODUCT_IMAGE,
                    InputOption::VALUE_NONE,
                    'Import Images'
                ),
                new InputOption(
                    self::ALLOW_COLOR,
                    self::POST_PRODUCT_COLOR,
                    InputOption::VALUE_NONE,
                    'Import Color'
                ),
 
            ]);
 
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $state = $this->objectManager->get('\\Magento\\Framework\\App\\State');
        $state->setAreaCode("adminhtml");
        $this->importproduct = $this->objectManager->get('\Themagnet\Productimport\Model\Importproduct');
        
        $this->_csvfiles = $this->objectManager->get('\Themagnet\Productimport\Model\Csvfiles');
        
        $allowSimple = $input->getOption(self::ALLOW_SIMPLE);
        $allowConfig = $input->getOption(self::ALLOW_CONFIG);
        $allowAttribute = $input->getOption(self::ALLOW_ATTRIBUTE);
        $allowPrice = $input->getOption(self::ALLOW_PRICE);
        $restartProcess = $input->getOption(self::ALLOW_RESET);
        $postProcess = $input->getOption(self::FILE_TRANSFER);
        $importImage = $input->getOption(self::PRODUCT_IMAGE);
        $importColor = $input->getOption(self::ALLOW_COLOR);
        $helper = $this->getHelper('question');
        if ($allowSimple) {
            if($this->_csvfiles->isLokFileExists('simple') !== true){
                $question = new ConfirmationQuestion('Are you sure you want to import simple product?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                        $this->importproduct->createSimpleFile($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Simple product imported.</info>');
                }
            }else{
                $this->customCommand->isErrorMessage($output, 'Simple product already imported');
                return false;
            }
        }elseif ($allowConfig) {
            if($this->customCommand->isCommandExecte($output, $this->_csvfiles) === false){
                return false;
            }
            if($this->_csvfiles->isLokFileExists('config') !== true){
                $question = new ConfirmationQuestion('Are you sure you want to import Config product?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->createConfigFile($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Config product imported.</info>');
                }
            }else{
                $this->customCommand->isErrorMessage($output, 'Config product already imported');
                return false;
            }
        }elseif ($allowAttribute){
            $question = new ConfirmationQuestion('Are you sure you want to import product attributs?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                        $this->importproduct->importProductAttributs($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                    $output->writeln('<info>Product attributs imported.</info>');
            }
        }elseif ($allowPrice){ 
            $question = new ConfirmationQuestion('Are you sure you want to import product advance pricing?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->importProductPrice($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Product advance pricing imported.</info>');
            }


        }elseif ($restartProcess){ 
            $question = new ConfirmationQuestion('Are you sure you want to clean currently created files in local dir?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->remaneProcessingFolder($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Folder clean successfully.</info>');
            }


        }elseif ($postProcess){ 
            $question = new ConfirmationQuestion('Are you sure you want to move FTP xml-updates file into xml-processed dir?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->postImport($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>XML files move to xml-processed.</info>');
            }


        }elseif ($importImage){ 
            $question = new ConfirmationQuestion('Are you sure you want to import product images?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->importImages($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Product Images imported successfully.</info>');
            }


        }elseif ($importColor){ 
            $question = new ConfirmationQuestion('Are you sure you want to import product color?[y/N]',false);
                if ($helper->ask($input, $output, $question) || !$input->isInteractive()) {
                    //$this->registry->register('isSecureArea',true);        
                    try {
                       $this->importproduct->importProductColor($output);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException($e->getMessage());
                    }
                   
                    //$this->registry->unregister('isSecureArea');
                    $output->writeln('<info>Product color imported successfully.</info>');
            }


        } else {
            throw new \InvalidArgumentException('Argument is missing.');
        }
    }

}