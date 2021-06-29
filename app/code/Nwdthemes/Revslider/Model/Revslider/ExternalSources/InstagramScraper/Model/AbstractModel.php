<?php

namespace Nwdthemes\Revslider\Model\Revslider\ExternalSources\InstagramScraper\Model;

use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\InstagramScraper\Traits\ArrayLikeTrait;
use \Nwdthemes\Revslider\Model\Revslider\ExternalSources\InstagramScraper\Traits\InitializerTrait;

/**
 * Class AbstractModel
 * @package InstagramScraper\Model
 */
abstract class AbstractModel implements \ArrayAccess
{
    use InitializerTrait, ArrayLikeTrait;

    /**
     * @var array
     */
    protected static $initPropertiesMap = [];

    /**
     * @return array
     */
    public static function getColumns()
    {
        return \array_keys(static::$initPropertiesMap);
    }
}