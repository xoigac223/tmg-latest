<?php

namespace Biztech\Productdesigner\Model\Entity\Attribute\Source;

class Designtemplates extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource {

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

    public function getAllOptions() {        

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $obj_product = $objectManager->create('Biztech\Productdesigner\Model\Designtemplatecategory')->getCollection();
       
        if (empty($this->_options)) {
            $this->_options = array();
            foreach ($obj_product as $obj) {
                $data = array(
                    'label' => __($obj->getCategoryTitle()),
                    'value' => $obj->getDesigntemplatescategoryId()
                );
                array_push($this->_options, $data);
            }
        }
        return $this->_options;
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
