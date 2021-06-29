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
namespace Blackbird\ContentManager\Model\ContentType\CustomField;

use Blackbird\ContentManager\Model\ResourceModel\ContentType\CustomField\Option as ResourceOption;

/**
 * Custom Field Model
 */
class Option extends \Blackbird\ContentManager\Model\AbstractModel 
    implements \Blackbird\ContentManager\Api\Data\ContentType\CustomField\OptionInterface
{
    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceOption::class);
        $this->setIdFieldName(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if (!$this->hasData(self::TITLE) && $this->hasData("label")) {
            $this->setTitle($this->getLabel());
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSortOrder()
    {
        return $this->_getData(self::SORT_ORDER);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData(self::TITLE);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_getData(self::VALUE);
    }

    /**
     * @return string
     */
    public function getDefault()
    {
        return $this->_getData(self::DEFAULT_VAL);
    }
}
