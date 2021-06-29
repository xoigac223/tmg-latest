<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class checkKey implements ObserverInterface {

    const XML_PATH_ACTIVATIONKEY = 'productdesigner/activation/key';
    const XML_PATH_DATA = 'productdesigner/activation/data';

    protected $_scopeConfig;
    protected $encryptor;
    protected $_configFactory;
    protected $_helper;
    protected $_objectManager;
    protected $_request;
    protected $_resourceConfig;
    protected $configModel;
    protected $_configValueFactory;
    protected $_zend;
    protected $_assetRepo;
    protected $_dir;



    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Encryption\EncryptorInterface $encryptor, \Magento\Config\Model\Config\Factory $configFactory, \Biztech\Productdesigner\Helper\Data $helper, \Magento\Framework\ObjectManagerInterface $objectmanager, \Magento\Framework\App\RequestInterface $request, \Zend\Json\Json $zend, \Magento\Config\Model\ResourceModel\Config $resourceConfig, \Magento\Framework\App\Config\ValueFactory $configValueFactory, \Magento\Config\Model\Config $configModel, \Magento\Framework\View\Asset\Repository $assetRepo, \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->_configFactory = $configFactory;
        $this->_helper = $helper;
        $this->_objectManager = $objectmanager;
        $this->_request = $request;
        $this->_zend = $zend;
        $this->_resourceConfig = $resourceConfig;
        $this->configModel = $configModel;
        $this->_configValueFactory = $configValueFactory;
        $this->_assetRepo = $assetRepo;
        $this->_dir = $dir;      
        $this->_storeManager = $helper->getStoreManager();
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
         if ($observer->getData()['website'] != '' || $observer->getData()['store'] != '') {             
            return;
        }
        $k = $this->_scopeConfig->getValue(self::XML_PATH_ACTIVATIONKEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $s = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.appjetty.com/extension/licence.php'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&domains=' . urlencode(implode(',', $this->_helper->getAllStoreDomains())) . '&sec=magento2-brush-your-ideas');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        //8dc8e35bdd101fa7687b9f48eaa4c8fd

        $content = curl_exec($ch);
        //$res1 = $this->_zend->decode($content);  
        $res1 = json_decode($content);
        $res = (array) $res1;
        // print_r(implode(',', $this->_helper->getAllStoreDomains())); 
        $modulestatus = $this->_resourceConfig;
        //$enc = $this->encryptor;   
        if (empty($res)) {
            $modulestatus->saveConfig('productdesigner/activation/key', "");
            $modulestatus->saveConfig('productdesigner/productdesigner_general/enabled', 0);
            $data = $this->_scopeConfig('productdesigner/activation/data');
            $this->_resourceConfig->saveConfig('productdesigner/activation/data', $data, 'default', 0);
            $this->_resourceConfig->saveConfig('productdesigner/activation/websites', '', 'default', 0);
            return;
        }
        $data = '';
        $web = '';
        $en = '';
        if (isset($res['dom']) && intval($res['c']) > 0 && intval($res['suc']) == 1) {
            $data = $this->encryptor->encrypt(base64_encode($this->_zend->encode($res1)));
            if (!$s) {
                $params = $this->_request->getParam('groups');
                if (isset($params['activation']['fields']['websites']['value'])) {
                    $s = $params['activation']['fields']['websites']['value'];
                }
            }
            $en = $res['suc'];
            if (isset($s) && $s != null) {
                $web = $this->encryptor->encrypt($data . implode(',', $s) . $data);
            } else {
                $web = $this->encryptor->encrypt($data . $data);
            }
        } else {
            $modulestatus->saveConfig('productdesigner/activation/key', "", 'default', 0);
            $modulestatus->saveConfig('productdesigner/productdesigner_general/enabled', 0, 'default', 0);
        }
        $this->_resourceConfig->saveConfig('productdesigner/activation/data', $data, 'default', 0);
        $this->_resourceConfig->saveConfig('productdesigner/activation/websites', $web, 'default', 0);
        $this->_resourceConfig->saveConfig('productdesigner/activation/en', $en, 'default', 0);
        $this->_resourceConfig->saveConfig('productdesigner/activation/installed', 1, 'default', 0);

        /*create dynamic brushyourideas.css*/

        $objectManagerConfig = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManagerConfig->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $primaryColor = $config->getValue('productdesigner/themedesigner_general/primary_background');
        $secondaryColor = $config->getValue('productdesigner/themedesigner_general/secondary_background');
        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        $css = $resultPage->setData(array("primary_background"=>$primaryColor,"secondary_background"=>$secondaryColor))->setTemplate('productdesigner/system/config/theme.phtml')->toHtml();
        

        $localeCode = $config->getValue('general/locale/code');

        $themeId = $this->_scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );

         $themeProvider = $this->_objectManager->create('Magento\Framework\View\Design\Theme\ThemeProviderInterface');
         $theme = $themeProvider->getThemeById($themeId);
         
        $cssfile = $this->_dir->getRoot().'/pub/static/frontend/'. $theme->getThemePath() . '/' .$localeCode.'/Biztech_Productdesigner/css/brushyourdesigner.css';
        file_put_contents($cssfile, $css);

        $cssfileMain = $this->_dir->getRoot().'/app/code/Biztech/Productdesigner/view/frontend/web/css/brushyourdesigner.css';
        file_put_contents($cssfileMain, $css);
        $command = 'php bin/magento cache:clean && php bin/magento cache:flush';
        shell_exec($command);
    }

}
