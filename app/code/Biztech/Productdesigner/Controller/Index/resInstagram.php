<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;


class resInstagram extends \Magento\Framework\App\Action\Action {


    
   

    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {

        
        $code = $this->getRequest()->getParam('code');
        $instagramResCurlUrl = $this->_url->getUrl('productdesigner/index/resInstagram');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $instaClientID = $config->getValue('productdesigner/social_media_upload/instagram_clientid');
        $instaClientSecret = $config->getValue('productdesigner/social_media_upload/instagram_clientsecret');


        $apiData = array(   
          'client_id'       => $instaClientID,
          'client_secret'   => $instaClientSecret,
          'grant_type'      => 'authorization_code',
          'redirect_uri'    => $instagramResCurlUrl,
          'code'            => $code
        );
        $apiHost = 'https://api.instagram.com/oauth/access_token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiHost);
        curl_setopt($ch, CURLOPT_POST, count($apiData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($apiData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $jsonData = curl_exec($ch);
        curl_close($ch);
        $accessPkg= (array)json_decode($jsonData);
        $accessToken = $accessPkg['access_token'];

        $user = (array)$accessPkg['user'];
      

        $layout = $this->_objectManager->create('Magento\Framework\View\LayoutInterface');
        $resultPage = $layout->createBlock('Biztech\Productdesigner\Block\Productdesigner');
        $data = $resultPage->setData(array("insta_code"=>$accessToken,"user_id"=>$user['id']))->setTemplate('productdesigner/social_media/instagram_child.phtml')->toHtml();

    }

}

