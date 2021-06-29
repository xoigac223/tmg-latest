<?php

/**
 * Copyright © 2017-2018 AppJetty. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Biztech\Productdesigner\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ImportImprintButton extends Field {

    /**
     * @var string
     */
    protected $_template = 'Biztech_Productdesigner::system/config/importimprint.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
    Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element) {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl() {
        return $this->getUrl('biztech_productdesigner/system_config/importimprintxml');
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock(
                        'Magento\Backend\Block\Widget\Button'
                )->setData(
                [
                    'id' => 'collect_button',
                    'label' => __('Import Imprint Areas'),
                    'onclick' => 'javascript:importimprintxml(); return false;'
                ]
        );

        return $button->toHtml();
    }

}
