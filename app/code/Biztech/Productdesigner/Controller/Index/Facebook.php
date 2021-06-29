<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

class Facebook extends \Magento\Framework\App\Action\Action {
    /**
     * Index action
     *
     * @return $this
     */
    public function execute() {
        $data = $this->getRequest()->getParams();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designimages\Collection')->addFieldToFilter('design_id',Array('eq' => $data['data']['design_id']))->addFieldToFilter('design_image_type','base');
		$designImages = $obj_product->getData();
        $design_name = 'Test Name';
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
    	$media_fb_path = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'productdesigner/designs/catalog/product/base'.$designImages[0]['image_path'];

        $result['p_name'] = $design_name;
        $result['p_logo'] = $media_fb_path;

        $result['p_url'] = $this->_url->getUrl('productdesigner')."?id=".$data['data']['product_id']."&design=".$data['data']['design_id'];
        $this->getResponse()->setBody(json_encode($result));
    }
}

