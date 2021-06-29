<?php

namespace Mirasvit\Sorting\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Sorting\Api\Data\CriterionInterface;

class Criterion extends AbstractModel implements CriterionInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Criterion::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($value)
    {
        return $this->setData(self::CODE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault()
    {
        return $this->getData(self::IS_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($value)
    {
        return $this->setData(self::IS_DEFAULT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isSearchDefault()
    {
        return $this->getData(self::IS_SEARCH_DEFAULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSearchDefault($value)
    {
        return $this->setData(self::IS_SEARCH_DEFAULT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($value)
    {
        return $this->setData(self::POSITION, $value);
    }


    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        try {
            return \Zend_Json::decode($this->getData(self::CONDITIONS_SERIALIZED));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConditions(array $value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, \Zend_Json::encode($value));
    }
}