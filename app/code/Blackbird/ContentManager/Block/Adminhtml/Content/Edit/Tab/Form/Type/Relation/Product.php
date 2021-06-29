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
namespace Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type\Relation;

class Product extends \Blackbird\ContentManager\Block\Adminhtml\Content\Edit\Tab\Form\Type\AbstractType
{
    /**
     * @var string
     */
    protected $_template = 'Blackbird_ContentManager::content/edit/tab/form/type/relation/product.phtml';
    
    /**
     * @return string
     */
    public function getUrlSource()
    {
        return $this->getUrl('contentmanager/product_widget/chooser', ['form' => $this->getElement()->getHtmlId()]);
    }
}
