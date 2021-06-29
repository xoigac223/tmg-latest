<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Biztech\Productdesigner\Controller\Index;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class addtocart extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    // protected $cart;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Catalog\Model\Product $product
    ) {
        $this->resultPageFactory = $resultPageFactory;

        //$this->cart = $cart;
        $this->product = $product;
        parent::__construct($context);
    }

    public function execute() {
        try {

            $params1 = $this->getRequest()->getParams();
            if (isset($params1['data']['colorid']))
                $color = $params1['data']['colorid'];
            $prod_id = $params1['data']['productid'];
            $design_id = $params1['data']['design'];
            if (isset($params1['data']['qty'])) {
                $qty = $params1['data']['qty'];
            }

           /* if (isset($params1['data']['custom'])) {

                $option = $params1['data']['custom'];

                $options = array();

                foreach ($option as $key => $value) {
                    if (array_key_exists($value['name'], $options)) {
                        $options[$value['name']] = array($options[$value['name']], $value['value']);
                    } else {
                        $options[$value['name']] = $value['value'];
                    }
                }
            }*/
            /*added by BC: AS Task: Add custom option from product desginer page*/
            if (isset($params1['data']['product_custom_options'])) {

                $option = $params1['data']['product_custom_options'];

                $options = array();

                foreach ($option as $key => $value) {
                    $value['name'] = intval(preg_replace('/[^0-9]+/', '', $value['name']));
                    if (array_key_exists($value['name'], $options)) {
                        $options[$value['name']] = array($options[$value['name']], $value['value']);
                    } else {
                        $options[$value['name']] = $value['value'];
                    }
                }
            }
            /*added by BC: AS Task: Add custom option from product desginer page*/
        

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            
            if ($params1['data']['producttype'] == 'configurable') {

                if (isset($params1['data']['configattr'])) {
                    $attr = $params1['data']['configattr'];
                    foreach ($attr as $key => $value) {

                        $params = array();
                        $params['product'] = $prod_id;
                        //$params['qty'] = 1; //product quantity
                        if (isset($options)) {
                            $params['options'] = $options;
                        }

                        $attrs = array();

                        $attrs[$color] = $params1['data']['color'];
                        if ($value['name'] == 'size-quantity') {
                            $test = '';
                            $test = $value['value'];
                        } else {
                            if (isset($test) && $test > 0) {
                                $params['product'] = $prod_id;
                                $params['qty'] = $test;

                                $attrs[$value['name']] = $value['value'];
                                $params['super_attribute'] = $attrs;
                                if (isset($options)) {
                                    $params['options'] = $options;
                                }
                               // print_r($params);
                                $customCart = $objectManager->create('\Magento\Checkout\Model\Cart');
                                $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                                $_product = $obj_product->load($prod_id);
                                $customCart->addProduct($_product, $params);
                            }
                        }
                    }
                    
                    //print_r($params['super_attribute']); die;
                    if (isset($customCart)) {

                        $customCart->save();
                        $response['url'] = $this->_url->getUrl('checkout/cart');
                        $response['status'] = 'success';
                        $this->messageManager->addSuccess(__('Add to cart successfully.'));
                    } else {
                        $response['status'] = 'fail';
                        $response['message'] = 'please provide quantity';
                    }
                } else {

                    $params = array();
                    $params['product'] = $prod_id;
                    $params['qty'] = $qty;
                    $attrs = array();
                    $attrs[$color] = $params1['data']['color'];
                    $params['super_attribute'] = $attrs;
                    if (isset($options)) {
                        $params['options'] = $options;
                    }
                    $customCart = $objectManager->create('\Magento\Checkout\Model\Cart');                    
                    $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                    $_product = $obj_product->load($prod_id);
                    $customCart->addProduct($_product, $params);
                    $customCart->save();
                    $response['url'] = $this->_url->getUrl('checkout/cart');
                    $response['status'] = 'success';
                    $this->messageManager->addSuccess(__('Add to cart successfully.'));
                }
            } else {
                $params = array();
                $params['product'] = $prod_id;
                $params['qty'] = $qty;
                if (isset($options)) {
                    $params['options'] = $options;
                }
                $customCart = $objectManager->create('\Magento\Checkout\Model\Cart');
                $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                $_product = $obj_product->load($prod_id);
                $customCart->addProduct($_product, $params);
                $carts = $customCart->getQuote();

                $customCart->save();
                $customCart->setCartWasUpdated(true);
                $response['url'] = $this->_url->getUrl('checkout/cart');
                $response['status'] = 'success';
                $this->messageManager->addSuccess(__('Add to cart successfully.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
//            $this->messageManager->addException(
//                    $e,
//                    __('%1',
//                            $e->getMessage())
//            );
            $response['message'] = $e->getMessage();
            $response['status'] = 'fail';
        } catch (\Exception $e) {

            $response['message'] = $e->getMessage();
            $response['status'] = 'fail';
//            $this->messageManager->addException($e,
//                    __('error.'));
        }
        /* cart page */
        $this->getResponse()->setBody(json_encode($response));
    }

}
