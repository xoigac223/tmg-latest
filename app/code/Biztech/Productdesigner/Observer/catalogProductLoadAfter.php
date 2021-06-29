<?php

namespace Biztech\Productdesigner\Observer;

use Magento\Framework\Event\ObserverInterface;

class catalogProductLoadAfter implements ObserverInterface {

    protected $_request;
    protected $_helper;

    public function __construct(
    \Magento\Framework\App\Request\Http $request, \Magento\Framework\Pricing\Helper\Data $helper
    ) {

        $this->_request = $request;
        $this->_helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {


        $action = $this->_request->getFullActionName();

        if ($action == 'catalog_product_view') {
            $product = $observer->getProduct();
            $product->setHasOptions(1);
        }
        if ($action == 'productdesigner_index_addtocart') {
            //$para = $this->_request()->getParams();
            $data = $this->_request->getPost();

            $used_color_count = $data['data']['used_color_count'];
            // $area_size_id = $_POST['data']['area_size_id'];
            $id = $data['data']['productid'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($id);
            $printing_surcharge = 0;

            if ($product->getprintingmethodattr() != '') {
                if ($product->getTypeId() == 'configurable') {
                    $printingCollection = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Printingmethod\Collection')->addFieldToFilter('status', array('eq' => 1));
                    if (count($printingCollection) != 0) {
                        $prining_code = $data['data']['printing_code'];
                        $printing_type_id = $data['data']['printing_type_id'];
                        $printing_surcharge = isset($data['data']['printing_surcharge']) ? $data['data']['printing_surcharge'] : 0;
                    }
                } else {
                    $simpleprintingCollection = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Simpleprintingmethod\Collection')->addFieldToFilter('status', array('eq' => 1));
                    if (count($simpleprintingCollection) != 0) {
                        $prining_code = $data['data']['printing_code'];
                        $printing_type_id = $data['data']['printing_type_id'];
                        $printing_surcharge = isset($data['data']['printing_surcharge']) ? $data['data']['printing_surcharge'] : 0;
                    }
                }
            }



            $para = $data['data']['design'];



            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($id);
            $product_type = $product->getTypeId();



            $item = $observer->getEvent()->getData('quote_item');

            $product = $observer->getEvent()->getData('product');
            $item = ( $item->getParentItem() ? $item->getParentItem() : $item );

           /* 
            $designModel = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designs\Collection')->addFieldToFilter('design_id', array('eq' => $para))->getData();
           $sub_total = json_decode($designModel[0]['prices'])->sub_total;
            $surcharge = 0;
            if ($product_type == "configurable") {
                if (isset($printing_type_id)) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $obj_printinmethod = $objectManager->create('Biztech\Productdesigner\Model\Printingmethod');
                    $printinmethod = $obj_printinmethod->load($printing_type_id);
                    $methodtype = $printinmethod->getColortype();
                    if ($methodtype == 1) {
                        $collection = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Colors\Collection')->addFieldToFilter('colors_counter', array('eq' => $used_color_count))->getFirstItem();
                        $surcharge = $collection->getColorsPrice();
                    } else {

                    }
                } else {
                    $surcharge = 0;
                }
            } else {
                if (isset($printing_type_id)) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $surcharge = $objectManager->create('Biztech\Productdesigner\Model\Simpleprintingmethod')->load($printing_type_id)->getFrontSurcharge();
                } else {
                    $surcharge = 0;
                }
            }


            if ($item->getProduct()->getTypeId() == 'configurable') {
                $params = $item->getProduct()->getCustomOptions();
                $eavAttribute = $objectManager->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
                $color_attributeId = $eavAttribute->getIdByCode('catalog_product', 'color');
                $size_attributeId = $eavAttribute->getIdByCode('catalog_product', 'size');
                foreach ($params as $key => $pram) {
                    if ($key == 'attributes') {
                        $designData = $pram->getData();

                        $designdata1 = unserialize($designData['value']);

                     
                         $cntAtrribute=count($designdata1);

                        $color = $designdata1[$color_attributeId];
                        if ($size_attributeId) {
                             if($cntAtrribute>1){$size = $designdata1[$size_attributeId];}   
                             
                           
                        }
                    }
                }
                $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
                $conf_product = $obj_product->load($item->getProduct()->getId());
                $productTypeInstance = $conf_product->getTypeInstance();
                $simpleproduct = '';

                $simpleCollection = $productTypeInstance->getUsedProductCollection($conf_product)
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('color', $color);
                if ($size_attributeId) {
                    if($cntAtrribute>1){$simpleCollection->addAttributeToFilter('size', $size);}
                }
                foreach ($simpleCollection as $simple) {
                    $simpleproduct = $simple;
                    break;
                }

                $price_object = $objectManager->get('Magento\Catalog\Model\Product\Type\Price');
                $base_price = $price_object->getFinalPrice($item->getQty(), $simpleproduct);
                $price = $base_price + $sub_total + $printing_surcharge;
                //$final_price = $base_price + $sub_total;
            } else {
                $price = $item->getProduct()->getFinalPrice() + $sub_total + $printing_surcharge;
                //$final_price = $item->getProduct()->getFinalPrice() + $sub_total;
            }*/

            if (isset($prining_code)) {
                $additionalOptions[] = array(
                    'product_id' => $id,
                    'code' => 'printing_method',
                    'label' => 'Printing Method',
                    'printing_code' => $prining_code,
                    'value' => $prining_code,
                    'custom_view' => false,
                );

                $additionalOptions[] = array(
                    'code' => 'printing_surcharge',
                    'label' => 'Printing Price',
                    'printing_type_id' => $printing_type_id,
                    'value' => $this->_helper->currency($printing_surcharge, true, false),
                    'custom_view' => false,
                );
            }

            if (isset($para)) {
                $additionalOptions[] = array(
                    'product_id' => $id,
                    'code' => 'product_design',
                    'label' => 'Product Design',
                    'design_id' => $para,
                    'value' => $para,
                    'custom_view' => false,
                );
            }



            $item = $observer->getQuoteItem();

            $item->addOption(
                    array(
                        'product_id' => $id,
                        'code' => 'additional_options',
                        'label' => 'Product Design',
                        'value' => serialize($additionalOptions),
                    )
            );

           /* $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);*/
            $item->getProduct()->setIsSuperMode(true);
        }
    }

}
