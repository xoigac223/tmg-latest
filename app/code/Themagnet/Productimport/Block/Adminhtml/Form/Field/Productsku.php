<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 * @package Atwix_DynamicFields
 */

namespace Themagnet\Productimport\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class AdditionalEmail
 */
class Productsku extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected $_addAfter = false;
    /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel = false;

    //protected $_template = 'Bullseye_Storepickup::system/config/country.phtml';

    protected function _prepareToRender()
    {
        $this->addColumn('blank_sku', ['label' => __('Product SKU'), 'class' => 'required-entry']);
        
        $this->_addAfter = false;
        $this->_addButtonLabel = false;
    }

}