<?php

namespace Mirasvit\Sorting\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class RankingFactor extends AbstractModel implements RankingFactorInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\RankingFactor::class);
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
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($value)
    {
        return $this->setData(self::TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isGlobal()
    {
        return $this->getData(self::IS_GLOBAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGlobal($value)
    {
        return $this->setData(self::IS_GLOBAL, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($value)
    {
        return $this->setData(self::WEIGHT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        try {
            return \Zend_Json::decode($this->getData(self::CONFIG_SERIALIZED));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig(array $value)
    {
        return $this->setData(self::CONFIG_SERIALIZED, \Zend_Json::encode($value));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData($key, $default = false)
    {
        $config = $this->getConfig();

        $value = isset($config[$key]) ? $config[$key] : false;

        return $value ? $value : $default;
    }
}