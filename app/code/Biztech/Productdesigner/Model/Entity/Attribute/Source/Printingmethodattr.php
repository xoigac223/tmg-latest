<?php

namespace Biztech\Productdesigner\Model\Entity\Attribute\Source;

class Printingmethodattr extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Retrieve all options array
     *
     * @return array
     */
    protected $request;
    protected $_options = [];

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    public function getAllOptions()
    {
        $product_id = $this->request->getParam('id');

        if ($product_id) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $obj_product      = $objectManager->create('Magento\Catalog\Model\Product');
            $product          = $obj_product->load($product_id);
            $this->_options[] = array('label' => "Please Select", 'value' => "0");
            if ($product->getTypeID() == "configurable") {
                $printmethods = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Printingmethod\Collection')->addFieldToFilter('status', 1);
                foreach ($printmethods as $printmethod) {
                    $methodid         = $printmethod->getPrintingId();
                    $methodname       = $printmethod->getPrintingName();
                    $methoddata[]     = array('label' => "$methodname", 'value' => "$methodid");
                    $this->_options[] = array('label' => "$methodname", 'value' => "$methodid");
                }
            } else {
                $simpleprinting_data    = $objectManager->create('Biztech\Productdesigner\Model\Mysql4\Simpleprintingmethod\Collection')->addFieldToFilter('status', 1);
                $simpleprinting_methods = array();
                foreach ($simpleprinting_data as $simpledata) {
                    $methodid         = $simpledata->getsimpleprinting_id();
                    $methodname       = $simpledata->getSimpleprintingName();
                    $methoddata[]     = array('label' => "$methodname", 'value' => "$methodid");
                    $this->_options[] = array('label' => "$methodname", 'value' => "$methodid");
                }

            }
        }
        $options = $this->_options;

        return $options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
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
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

}
