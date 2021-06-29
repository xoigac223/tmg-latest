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

/**
 * Build renderer as the core field type asbtract 'file'
 */
abstract class AbstractFile extends \Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type\AbstractType
{
    /**
     * Retrieves the additional path where the images are saved
     * 
     * @return string
     */
    protected function getAddtionalPath()
    {
        $path = '';
        
        // Retrieve the additionnal file path
        if (!empty($this->getCustomField()->getFilePath())) {
            $path = $this->getCustomField()->getFilePath();
            if (substr($path, -1) !== '/') {
                $path .= '/';
            }
        }
        
        return $path;
    }
}
