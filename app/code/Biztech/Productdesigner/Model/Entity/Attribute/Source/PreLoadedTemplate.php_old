<?php

namespace Biztech\Productdesigner\Model\Entity\Attribute\Source;

class PreLoadedTemplate extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {

    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected $request;

    public function __construct(
    \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function getAllOptions() {
        $product_id = $this->request->getParam('id');

        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($product_id);
            // $product = Mage::getModel('catalog/product')->load($product_id);
            $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Designtemplates\Collection')->addFieldToFilter('product_id',$product_id);
            $designs = $obj_product->getData();
            //print_r($designs); die;
            if (is_null($this->_options)) {
                $this->_options = array();
                $nodata = array(
                    'label' => __('Select Default template for this product'),
                    'value' => ""
                );
                array_push($this->_options,
                        $nodata);
                if (!empty($designs)) {
                    foreach($designs as $design)
                        {
                            
                            $image = 0;
                            $text = 0;
                            $layers = json_decode($design['layer_images']);
                            foreach ($layers as $key => $value) 
                            {
                                if($value->type == 'image')
                                {
                                    $image = 1;
                                }
                                if($value->type == 'text' || $value->type == 'group')
                                {
                                    $text = 1;
                                }
                            }
                            $designName = $product->getName().' with ';
                            if($image && !$text)
                                $designName .= 'Image';
                            if($text && !$image)
                                $designName .= 'Text';
                            if($text && $image)
                                $designName .= 'Image & Text';
                            $data = array(
                                'label' => $designName,
                                'value' =>  $design['designtemplates_id']
                            );
                            array_push($this->_options, $data);
                        }
                }
            }
            return $this->_options;
        }
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray() {
        $_options = array();
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value) {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

}
