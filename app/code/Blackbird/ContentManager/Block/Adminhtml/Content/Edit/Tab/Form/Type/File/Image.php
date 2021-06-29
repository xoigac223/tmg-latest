<?php
/**
 * Blackbird ContentManager Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManager
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Blackbird Team
 * @license         https://www.advancedcontentmanager.com/license/
 */
namespace Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type\File;

use Blackbird\ContentManager\Model\ContentType;

class Image extends \Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type\File\AbstractFile
{
    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/edit/tab/form/type/file/image.phtml';
    
    /**
     * @return string
     */
    public function getOrigFileName()
    {
        $data = $this->getContentField();
        $filename = '';
        
        if (isset($data[$this->getCustomField()->getIdentifier() . '_orig'])) {
            $filename = $data[$this->getCustomField()->getIdentifier() . '_orig'];
        }
        
        return $filename;
    }
    
    /**
     * @return string
     */
    public function getOrigImageUrl()
    {
        $data = $this->getContentField();
        $filename = isset($data[$this->getCustomField()->getIdentifier() . '_orig']) ? $data[$this->getCustomField()->getIdentifier() . '_orig'] : '';
        $path = '';
        
        if (!empty($filename)) {
            $path = ContentType::CT_FILE_FOLDER . $this->getAddtionalPath();
            $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path . $filename;
        }
        
        return $path;
    }
    
    /**
     * @return string
     */
    public function getCropedImageUrl()
    {
        $data = $this->getContentField();
        $filename = isset($data[$this->getCustomField()->getIdentifier()]) ? $data[$this->getCustomField()->getIdentifier()] : '';
        $path = '';
        
        if (!empty($filename)) {
            $path = ContentType::CT_FILE_FOLDER . ContentType::CT_IMAGE_CROPPED_FOLDER . $this->getAddtionalPath();
            $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path . $filename;
        }
        
        return $path;
    }
    
    /**
     * Retrieves the params for the js script
     * 
     * @return string
     */
    public function getJsonParams()
    {
        $data = [
            'image' => $this->getOrigImageUrl(),
            'ghost' => false,
            'originalsize' => false,
            'ajax' => false,
            'resize' => true,
            'editstart' => true,
            'saveOriginal' => true,
            'editcrop' => false,
        ];
        
        // Manage width and height
        if ($this->getCustomField()->getCropW()) {
            $data['width'] = $this->getCustomField()->getCropW();
        }
        if ($this->getCustomField()->getCropH()) {
            $data['height'] = $this->getCustomField()->getCropH();
        }
        if (empty($data['width']) && empty($data['height'])) {
            $data['width'] = '400';
            $data['height'] = '350';
        }
        
        // If crop tool is disabled
        if (!$this->getCustomField()->getCrop()) {
            $data['buttonEdit'] = false;
            $data['buttonZoomin'] = false;
            $data['buttonZoomout'] = false;
            $data['buttonZoomreset'] = false;
            $data['buttonCancel'] = true;
            $data['buttonDone'] = true;
            $data['buttonDel'] = true;
            $data['editstart'] = false;
            $data['image'] = $this->getOrigImageUrl() . '?v=' . time();
        } else {
            if (!empty($this->getCropedImageUrl())) {
                $data['image'] = $this->getCropedImageUrl() . '?v=' . time();
            }

            $data['buttonZoomin'] = false;
            $data['buttonZoomout'] = false;
            $data['editcrop'] = true;
        }
        
        return json_encode($data);
    }
}
