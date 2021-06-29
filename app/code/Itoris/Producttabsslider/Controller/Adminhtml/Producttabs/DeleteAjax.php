<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */


namespace Itoris\Producttabsslider\Controller\Adminhtml\Producttabs;


class DeleteAjax extends \Magento\Backend\App\Action
{
    const BLOCK_HTML_CACHE_TAG = 'BLOCK_HTML';
    protected function cleanCashe(){

        $cacheFrontendPool = $this->_objectManager->get('Magento\Framework\App\Cache\Frontend\Pool');
        foreach($cacheFrontendPool as $cacheFrontend){
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_ALL, self::BLOCK_HTML_CACHE_TAG);
        }

    }
    public function execute()
    {
        $jsonFactory = $this->_objectManager->create('\Magento\Framework\Controller\Result\JsonFactory');
        $resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
        $conn = $resource->getConnection();
        if($this->getRequest()->getParam('id')){
            $sql = "DELETE FROM `{$resource->getTableName('itoris_producttabs_tabs')}`
                WHERE   tab_id = ".$this->getRequest()->getParam('id');
            $conn->query($sql);
        }
        $result = $jsonFactory->create();
        $this->cleanCashe();
        return $result->setData(['success' => true]);
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }

}