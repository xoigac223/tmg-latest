<?php
namespace Themagnet\Zoomcatalog\Model\Config\Source;
class Service implements \Magento\Framework\Option\ArrayInterface
{
    const CATALOG_ALL = 'catalogs/all';
    const CATALOG = 'catalogs';
    const FLYERS = 'flyers';
    const CATALOG_PERSONALIZED = 'catalogs/personalized';
    const FLYERS_PERSONALIZED = 'flyers/personalized';
    public function toOptionArray()
    {
        return array(
            array('value' => self::CATALOG_ALL, 'label' => __('Catalogs All')),
            array('value' => self::CATALOG, 'label' => __('Catalogs')),
            array('value' => self::FLYERS, 'label' => __('Flyers')),
            array('value' => self::CATALOG_PERSONALIZED, 'label' => __('Catalogs Personalized')),
            array('value' => self::FLYERS_PERSONALIZED, 'label' => __('Catalogs flyers'))
        );
    }
}