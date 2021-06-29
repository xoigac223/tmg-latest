<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Block\Widget;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context      
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager 
     * @param \Ves\Megamenu\Helper\Data                        $helper       
     * @param \Ves\Megamenu\Model\Menu                         $menu         
     * @param array                                            $data         
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Customer\Model\Session $customerSession,
        \Ves\Megamenu\Helper\MobileDetect $mobileDetectHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
        ) {
        parent::__construct($context);
        $this->_helper          = $helper;
        $this->_menu            = $menu;
        $this->_mobileDetect    = $mobileDetectHelper;
        $this->setTemplate("widget/menu.phtml");
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Ves\Megamenu\Model\Menu::CACHE_WIDGET_TAG,
            ], ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $menuId = $this->getData('id');
        $menuId = $menuId?$menuId:0;
        $code = $this->getConfig('alias');

        $conditions = $code.".".$menuId;

        return [
        'VES_MEGAMENU_MENU_WIDGET',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
        'template' => $this->getTemplate(),
        $conditions
        ];
    }


    public function _toHtml(){
        $html = $menu = '';
        $menu = $this->getMenuProfile($this->getData('id'), $this->getData('alias'));
        if($menu){
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->_customerSession->getCustomerGroupId();
            if(is_array($customerGroups) && !in_array($customerGroupId, $customerGroups)) {
                return;
            }
            $this->setData("menu", $menu);
        }

        $html = parent::_toHtml();
        $is_minify_html = true;
        if($is_minify_html) {
            $html = $this->_helper->minify_html($html);
        }
        return $html;
    }

    public function getMobileDetect() {
        return $this->_mobileDetect;
    }
    public function getMenuProfile($menuId = 0, $alias = ""){
        $menu = false;
        $store = $this->_storeManager->getStore();
        if($menuId){
            $menu = $this->_menu->setStore($store)->load((int)$menuId);
            if ($menu->getId() != $menuId) {
                $menu = false;
            }
        } elseif($alias){
            $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            if ($menu->getAlias() != $alias) {
                $menu = false;
            }
        }
        if ($menu && !$menu->getStatus()) {
            $menu = false;
        }
        return $menu;
    }
    public function getMobileTemplateHtml($menuAlias, $menu = null)
    {
        $html = '';
        if($menu) {
            $html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('menu', $menu)->toHtml();
        } else if($menuAlias){
            $html = $this->getLayout()->createBlock('Ves\Megamenu\Block\MobileMenu')->setData('alias', $menuAlias)->toHtml();
        }
        
        return $html;
    }
    public function getConfig($key, $default = NULL){
        if($this->hasData($key)){
            return $this->getData($key);
        }
        return $default;
    }
}