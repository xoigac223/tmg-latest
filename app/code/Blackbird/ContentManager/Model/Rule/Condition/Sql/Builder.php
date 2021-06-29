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
namespace Blackbird\ContentManager\Model\Rule\Condition\Sql;

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class SQL Builder
 */
class Builder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    /**
     * @param AbstractCondition $condition
     * @param string $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlCondition(AbstractCondition $condition, $value = '')
    {
        $sqlCondition = '';
        $argument = $condition->getMappedSqlField();
        $defaultArgument = 0;
        
        if ($condition->getMappedSqlDefaultField()) {
            $defaultArgument = $this->_connection->quoteIdentifier($condition->getMappedSqlDefaultField());
        }
        
        if ($argument) {
            $conditionOperator = $condition->getOperatorForValidate();

            if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
            }

            $sql = str_replace(
                ':field',
                $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument), $defaultArgument),
                $this->_conditionOperatorMap[$conditionOperator]
            );

            $sqlCondition = $this->_expressionFactory->create(
                ['expression' => $value . $this->_connection->quoteInto($sql, $condition->getBindArgumentValue())]
            );
        }
        
        return $sqlCondition;
    }
}
