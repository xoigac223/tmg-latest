<?php

namespace Biztech\Productdesigner\Block;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;


class Addtocart extends Field {

    protected $_fillLeadingZeros = true;
    protected $_catalogProductOptionTypeDate;
    protected $_option;
    protected $_product;
    
    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
            \Magento\Catalog\Model\Product\Option\Type\Date $catalogProductOptionTypeDate,
            \Magento\Catalog\Model\Product\Option $option,
            \Magento\Catalog\Model\Product $product,
            array $data = []
    ) {
        $this->_catalogProductOptionTypeDate = $catalogProductOptionTypeDate;
        $this->_product = $product;
        $this->_option = $option;
        parent::__construct($context,
                $data);
    }

    /**
     * @var PropertyLocker
     */

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function useCalendar()
    {
        return $this->_catalogProductOptionTypeDate->useCalendar();
    }
    
    public function getDateHtml() {
        if ($this->useCalendar()) {
            return $this->getCalendarDateHtml();
        } else {
            return $this->getDropDownsDateHtml();
        }
    }
    
    public function getCalendarDateHtml()
    {
        //$option = $this->getOption();
        
        
        $value = $this->_product->getPreconfiguredValues()->getData('options/' . $this->_option->getOptionId() . '/date');

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();

        $calendar = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Date'
        )->setId(
            'options_' . $this->_option->getOptionId() . '_date'
        )->setName(
            'options[' . $this->_option->getOptionId() . '][date]'
        )->setClass(
            'product-custom-option datetime-picker input-text'
        )->setImage(
            $this->getViewFileUrl('Magento_Theme::calendar.png')
        )->setDateFormat(
            $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT)
        )->setValue(
            $value
        )->setYearsRange(
            $yearStart . ':' . $yearEnd
        );

        return $calendar->getHtml();
    }
    
    public function getDropDownsDateHtml()
    {
        $fieldsSeparator = '&nbsp;';
        $fieldsOrder = $this->_catalogProductOptionTypeDate->getConfigData('date_fields_order');
        $fieldsOrder = str_replace(',', $fieldsSeparator, $fieldsOrder);

        $monthsHtml = $this->_getSelectFromToHtml('month', 1, 12);
        $daysHtml = $this->_getSelectFromToHtml('day', 1, 31);

        $yearStart = $this->_catalogProductOptionTypeDate->getYearStart();
        $yearEnd = $this->_catalogProductOptionTypeDate->getYearEnd();
        $yearsHtml = $this->_getSelectFromToHtml('year', $yearStart, $yearEnd);

        $translations = ['d' => $daysHtml, 'm' => $monthsHtml, 'y' => $yearsHtml];
        return strtr($fieldsOrder, $translations);
    }
    
     public function getTimeHtml()
    {
        if ($this->_catalogProductOptionTypeDate->is24hTimeFormat()) {
            $hourStart = 0;
            $hourEnd = 23;
            $dayPartHtml = '';
        } else {
            $hourStart = 1;
            $hourEnd = 12;
            $dayPartHtml = $this->_getHtmlSelect(
                'day_part'
            )->setOptions(
                ['am' => __('AM'), 'pm' => __('PM')]
            )->getHtml();
        }
        $hoursHtml = $this->_getSelectFromToHtml('hour', $hourStart, $hourEnd);
        $minutesHtml = $this->_getSelectFromToHtml('minute', 0, 59);

        return $hoursHtml . '&nbsp;<b>:</b>&nbsp;' . $minutesHtml . '&nbsp;' . $dayPartHtml;
    }
    
    protected function _getSelectFromToHtml($name, $from, $to, $value = null)
    {
        $options = [['value' => '', 'label' => '-']];
        for ($i = $from; $i <= $to; $i++) {
            $options[] = ['value' => $i, 'label' => $this->_getValueWithLeadingZeros($i)];
        }
        return $this->_getHtmlSelect($name, $value)->setOptions($options)->getHtml();
    }
    
     protected function _getHtmlSelect($name, $value = null)
    {
         

        $this->setSkipJsReloadPrice(1);

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setId(
            'options_' . $this->_option->getOptionId() . '_' . $name
        )->setClass(
            'product-custom-option admin__control-select datetime-picker' . $require
        )->setExtraParams()->setName(
            $name
        );

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-role="calendar-dropdown" data-calendar-role="' . $name . '"';
        $select->setExtraParams($extraParams);
        
        if ($value === null) {
            $value = $this->_product->getPreconfiguredValues()->getData(
                'options/' . $this->_option->getOptionId() . '/' . $name
            );
        }
        if ($value !== null) {
            $select->setValue($value);
        }

        return $select;
    }
    
    protected function _getValueWithLeadingZeros($value)
    {
        if (!$this->_fillLeadingZeros) {
            return $value;
        }
        return $value < 10 ? '0' . $value : $value;
    }

    protected function _prepareForm() {

        return parent::_prepareForm();
    }

}