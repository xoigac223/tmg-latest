<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Biztech\Productdesigner\Block\Cart\Item\Renderer\Actions;

class Edit extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $view = $config->getValue('productdesigner/selectview/Selectview');        
        $params = $this->getItem()->getProduct()->getCustomOptions();
        foreach($params as $key => $pram)
        {
            foreach ($params as $key => $pram) {
        if($key == 'additional_options'){
        $designData = $pram->getData();
        $designdata1 = unserialize($designData['value']);
        foreach($designdata1 as $dData){
            if($dData['code'] == 'product_design'){
                $design_id = $dData['design_id'];            
            }
            else{
                $design_id = '';
            }
        }
    }
    else{
        $design_id = '';
    }

}
        }
        if($design_id != '')
        {
            if($view == 'box_view')
            {
            return $this->getUrl(
            'productdesigner/index/index',
            [
                'id' => $this->getItem()->getProduct()->getId(),
                'design' => $design_id
            ]
        );
        } else 
        {
            return $this->getUrl(
            'productdesigner/index/full',
            [
                'id' => $this->getItem()->getProduct()->getId(),
                'design' => $design_id
            ]
        );
        }
        }
        else 
        {       
        return $this->getUrl(
            'checkout/cart/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId()
            ]
        );
    }
    }
}
