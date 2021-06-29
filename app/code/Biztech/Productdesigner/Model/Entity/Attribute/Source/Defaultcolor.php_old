<?php

namespace Biztech\Productdesigner\Model\Entity\Attribute\Source;

class Defaultcolor extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {

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
        $colors = [];

        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $obj_product = $objectManager->create('Magento\Catalog\Model\Product');
            $product = $obj_product->load($product_id);
            // $product = Mage::getModel('catalog/product')->load($product_id);
            $product_type = $product->getTypeId();

            if ($product_type == 'configurable') {
                $attrs = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);

                foreach ($attrs as $attr) {
                    if (0 == strcmp("color", $attr['attribute_code'])) {
                        $colors = $attr['values'];
                    }
                }
                if (is_null($this->_options)) {
                    $this->_options = array();
                    $nodata = array(
                        'label' => __('Select Default Color for this Design'),
                        'value' => ""
                    );
                    array_push($this->_options, $nodata);
                    foreach ($colors as $color) {
                        $data = array(
                            'label' => __($color['label']),
                            'value' => $color['value_index']
                        );
                        array_push($this->_options, $data);
                    }
                } else {
                    
                }
                return $this->_options;
            }
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
