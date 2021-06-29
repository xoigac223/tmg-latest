<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Themagnet\Productimport\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Connect extends Field
{
     /**
     * @var string
     */
    protected $_template = 'Themagnet_Productimport::system/config/connect.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Themagnet\Productimport\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('themagnet_productimport/system_config/connect');
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        // $ftp_server = $this->_helper->getConfig('themagnet/general/ftp_host');
        // $ftp_username = $this->_helper->getConfig('themagnet/general/ftp_username');
        // $ftp_userpass = $this->_helper->getConfig('themagnet/general/ftp_password');
        // $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
        // $login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);

        // // get file list of current directory
        // ftp_pasv($ftp_conn, true);

        // if(is_array(ftp_nlist($ftp_conn, ".")))
        // {
            $button = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'id' => 'connect_button',
                    'label' => __('Connect'),
                ]
            );

        return $button->toHtml();
    }
}
?>