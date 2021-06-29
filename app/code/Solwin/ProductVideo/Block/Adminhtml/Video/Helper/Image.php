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
 */
class Image extends \Magento\Framework\Data\Form\Element\Image
{
    /**
     * Video image model
     * 
     * @var \Solwin\ProductVideo\Model\Video\Image
     */
    protected $_imageModel;

    /**
     * constructor
     * 
     * @param \Solwin\ProductVideo\Model\Video\Image $imageModel
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Solwin\ProductVideo\Model\Video\Image $imageModel,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data
    ) {
        $this->_imageModel = $imageModel;
        parent::__construct(
                $factoryElement,
                $factoryCollection,
                $escaper,
                $urlBuilder,
                $data
                );
    }

    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->_imageModel->getBaseUrl().$this->getValue();
        }
        return $url;
    }
}