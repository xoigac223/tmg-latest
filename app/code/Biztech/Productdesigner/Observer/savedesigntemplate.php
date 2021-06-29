<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class savedesigntemplate implements ObserverInterface {

    protected $_request;

    //protected $_objectManager;
    protected $_object;
    public function __construct(
    \Magento\Framework\App\RequestInterface $request,
            
            \Magento\Framework\ObjectManagerInterface $object
    ) {
        $this->_request = $request;
        $this->_object = $object;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getEntityId();
       
        $data = $this->_request->getPost();
        $template_data=array();
        if (isset($data['product']['design_templates']) && $data['product']['design_templates'] && ($productId)) {
            $designTemplateArray=$data['product']['design_templates'];
            $template_data['templates'] = implode(',', $designTemplateArray);
            
            $template_data['product_id'] = $productId;
            $pro_temModel =  $this->_object->create('Biztech\Productdesigner\Model\Producttemplate')->load($productId,'product_id');

            //$pro_temModel = Mage::getModel('productdesigner/producttemplate')->load($productId,'product_id');

                    
             $templateModel = $this->_object->create('Biztech\Productdesigner\Model\Producttemplate')->load($pro_temModel->getProductTemplateId());
            //$templateModel = Mage::getModel('productdesigner/producttemplate')->load($pro_temModel->getProductTemplateId());

            

            $templateModel->setData($template_data)->setId($pro_temModel->getProductTemplateId());
            $templateModel->save();
        }
    }

}
