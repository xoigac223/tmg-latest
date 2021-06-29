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

interface ConfigInterface
{
    /**
     * Get configuration of contenttype field type by name
     *
     * @param string $name
     * @return array
     */
    public function getField($name);

    /**
     * Get configuration of all registered contenttype field types
     *
     * @return array
     */
    public function getAll();
}
