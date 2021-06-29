<?php
/**
 * Copyright © 2017-2018 AppJetty. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Controller\Index;

header("Access-Control-Allow-Origin: *");
class Sendmail extends \Magento\Framework\App\Action\Action { 

    /**
     * Index action
     *
     * @return $this
     */
    const XML_PATH_EMAIL_TEMPLATE_FIELD = 'section/group/your_email_template_field_id';

    /* Here section and group refer to name of section and group where you create this field in configuration */

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var string
     */
    protected $temp_id;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, 
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_scopeConfig = $context;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct(
            $context
        );
    }

    public function execute() {
        $data = $this->getRequest()->getParams();
        $friendMail = $data['data']['mail'];        
        $design_id = $data['data']['design_id'];
        $product_id = $data['data']['product_id'];        
        // get design
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id',Array('eq' => $design_id))->addFieldToFilter('design_image_type','base');
        $designImages = $obj_product->getData();
        
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $media_fb_path = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'productdesigner/designs/catalog/product/base'.$designImages[0]['image_path'];
        
        $mediUrl = $this->_url->getUrl('productdesigner')."?id=".$product_id."&design=".$design_id;
        // send mail
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $toEmail = $config->getValue('trans_email/ident_sales/email');
        $toName = $config->getValue('trans_email/ident_sales/name');

        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());
        $templateVars = array(
            'store' => $storeManager->getStore(),
            'media_fb_path' => $media_fb_path,
            'mediUrl'   => $mediUrl
        );
        $from = array('email' => $toEmail, 'name' => $toName);
        $inlineTranslation = $objectManager->get('\Magento\Framework\Translate\Inline\StateInterface');
        $inlineTranslation->suspend();
        $to = array($friendMail);
        $transport = $this->_transportBuilder->setTemplateIdentifier('biztech_productdesigner')
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($to)
                        ->getTransport();
        $transport->sendMessage();
        $inlineTranslation->resume();
        $result = array();
        $result['status'] = 'success';
        $this->getResponse()->setBody(json_encode($result));
    }
}