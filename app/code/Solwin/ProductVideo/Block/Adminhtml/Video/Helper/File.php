<?php
/**
 * Solwin Infotech
 * Solwin Advanced Product Video Extension
 *
 * @category   Solwin
 * @package    Solwin_ProductVideo
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
namespace Solwin\ProductVideo\Block\Adminhtml\Video\Helper;

use Magento\Framework\Data\Form\Element\CollectionFactory;

/**
 * @method string getValue()
 * @method bool getDisabled()
 * @method File setExtType(\string $extType)
 */
class File extends \Magento\Framework\Data\Form\Element\File
{
    /**
     * Video file model
     *
     * @var \Solwin\ProductVideo\Model\Video\File
     */
    protected $_fileModel;

    /**
     * constructor
     *
     * @param \Solwin\ProductVideo\Model\Video\File $fileModel
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Solwin\ProductVideo\Model\Video\File $fileModel,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data
    ) {
        $this->_fileModel = $fileModel;
        parent::__construct(
                $factoryElement,
                $factoryCollection,
                $escaper,
                $data
                );
        $this->setType('file');
        $this->setExtType('file');
    }

    /**
     * get the element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $this->addClass('input-file');
        $html .= parent::getElementHtml();
        if ($this->getValue()) {
            $url = $this->getUrl();
            if (!preg_match("/^http\:\/\/|https\:\/\//", $url)) {
                $url = $this->_fileModel->getBaseUrl() . $url;
            }
            $html .= '<br /><video controls="" width="350" height="auto" style="margin: 10px;"><source src="'.$url.'" type="video/mp4">
                                                </video><br><a href="'.$url.'">'.$this->getUrl().'</a> ';
        }
        $html .= $this->getDeleteCheckbox();
        return $html;
    }

    /**
     * get the delete checkbox html
     *
     * @return string
     */
    protected function getDeleteCheckbox()
    {
        $html = '';
        if ($this->getValue()) {
            $label = __('Delete File');
            $html .= '<br><span class="delete-image">';
            $html .= '<input type="checkbox" name="'.
                parent::getName().'[delete]" value="1" class="checkbox" id="'.
                $this->getHtmlId().'_delete"'.($this->getDisabled()
                        ? ' disabled="disabled"': '').'/>';
            $html .= '<label for="'.$this->getHtmlId().'_delete"'
                    .($this->getDisabled() ? ' class="disabled"' : '').'>';
            $html .= $label.'</label>';
            $html .= $this->getHiddenInput();
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * get hidden input with the value
     *
     * @return string
     */
    protected function getHiddenInput()
    {
        return '<input type="hidden" name="'.parent::getName().'[value]" '
                . 'value="'.$this->getValue().'" />';
    }

    /**
     * @return string
     */
    protected function getUrl()
    {
        return $this->getValue();
    }

    /**
     * get field name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->getData('name');
    }
}