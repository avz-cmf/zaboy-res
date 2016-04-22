<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\ConditionBuilder;

use zaboy\res\DataStore\DataStoresException;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * Make string with conditions for Query
 *
 * Format of this string depends on implementation
 *
 * @todo Data type fore Xiag\Rql\Parser\DataType\DateTime
 * @see zaboy\res\DataStore\ConditionBuilder\PhpConditionBuilder
 * @see zaboy\res\DataStore\ConditionBuilder\PhpConditionBuilder
 * @see zaboy\res\DataStore\ConditionBuilder\SqlConditionBuilder
 */
abstract class ConditionBuilderAbstract
{

    protected $literals = [
        'LogicOperator' => [
        ],
        'ArrayOperator' => [
        ],
        'ScalarOperator' => [
            'ge' => ['before' => '(', 'between' => '=>', 'after' => ')'],
            'gt' => ['before' => '(', 'between' => '>', 'after' => ')'],
            'le' => ['before' => '(', 'between' => '<=', 'after' => ')'],
            'lt' => ['before' => '(', 'between' => '<', 'after' => ')'],
        ]
    ];

    /**
     * @var string Contition if Query === null
     */
    protected $emptyCondition = ' true ';

    /**
     * Prepare fild name for using in condition
     *
     * It may be quoting for example
     *
     * @param string $fildName
     * @return string
     */
    public function prepareFildName($fildName)
    {
        return $fildName;
    }

    /**
     * Prepare fild value for using in condition
     *
     * It may be quoting for example
     *
     * @param string $fildValue
     * @return string
     */
    public function prepareFildValue($fildValue)
    {
        if (is_a($fildValue, 'Xiag\Rql\Parser\DataType\Glob', true)) {
            return $this->getValueFromGlob($fildValue);
        } else {
            return $fildValue;
        }
    }

    /**
     * Make string with conditions for any supported Query
     *
     * @param AbstractQueryNode $rootQueryNode
     * @return string
     */
    public function __invoke(AbstractQueryNode $rootQueryNode = null)
    {
        if (isset($rootQueryNode)) {
            return $this->makeAbstractQueryOperator($rootQueryNode);
        } else {
            return $this->emptyCondition;
        }
    }

    /**
     * Make string with conditions for not null Query
     *
     * @param AbstractQueryNode $queryNode
     * @return string
     * @throws DataStoresException
     */
    public function makeAbstractQueryOperator(AbstractQueryNode $queryNode)
    {
        switch (true) {
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode', true):
                return $this->makeScalarOperator($queryNode);
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode', true):
                return $this->makeLogicOperator($queryNode);
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode', true):
                return $this->makeArrayOperator($queryNode);
            default:
                throw new DataStoresException(
                'The Node type not suppoted: ' . $queryNode->getNodeName()
                );
        }
    }

    /**
     * Make string with conditions for LogicOperatorNode
     *
     * @param AbstractLogicOperatorNode $node
     * @return string
     */
    public function makeLogicOperator(AbstractLogicOperatorNode $node)
    {
        $nodeName = $node->getNodeName();
        if (!isset($this->literals['LogicOperator'][$nodeName])) {
            throw new DataStoresException(
            'The Logic Operator not suppoted: ' . $nodeName
            );
        }
        $arrayQueries = $node->getQueries();
        $strQuery = $this->literals['LogicOperator'][$nodeName]['before'];
        foreach ($arrayQueries as $queryNode) {
            /* @var $queryNode AbstractQueryNode */
            $strQuery = $strQuery
                    . $this->makeAbstractQueryOperator($queryNode)
                    . $this->literals['LogicOperator'][$nodeName]['between'];
        }
        $strQuery = rtrim($strQuery, $this->literals['LogicOperator'][$nodeName]['between']);
        $strQuery = $strQuery . $this->literals['LogicOperator'][$nodeName]['after'];
        return $strQuery;
    }

    /**
     * Make string with conditions for ScalarOperatorNode
     *
     * @param AbstractScalarOperatorNode $node
     * @return string
     */
    public function makeScalarOperator(AbstractScalarOperatorNode $node)
    {
        $nodeName = $node->getNodeName();
        if (!isset($this->literals['ScalarOperator'][$nodeName])) {
            throw new DataStoresException(
            'The Scalar Operator not suppoted: ' . $nodeName
            );
        }
        $value = $node->getValue() instanceof \DateTime ? $node->getValue()->format("Y-m-d") : $node->getValue();
        $strQuery = $this->literals['ScalarOperator'][$nodeName]['before']
                . $this->prepareFildName($node->getField())
                . $this->literals['ScalarOperator'][$nodeName]['between']
                . $this->prepareFildValue($value)
                . $this->literals['ScalarOperator'][$nodeName]['after'];
        return $strQuery;
    }

    /**
     * Make string with conditions for ArrayOperatorNode
     *
     * @param AbstractArrayOperatorNode $node
     * @return string
     */
    public function makeArrayOperator(AbstractArrayOperatorNode $node)
    {
        $nodeName = $node->getNodeName();
        if (!isset($this->literals['ArrayOperator'][$nodeName])) {
            throw new DataStoresException(
            'The Array Operator not suppoted: ' . $nodeName
            );
        }
        $arrayValues = $node->getValues();
        $strQuery = $this->literals['ArrayOperator'][$nodeName]['before'];
        foreach ($arrayValues as $value) {
            /* @var $queryNode AbstractQueryNode */
            $strQuery = $strQuery
                    . $this->prepareFildValue($value)
                    . $this->literals['ArrayOperator'][$nodeName]['between'];
        }
        $strQuery = rtrim($strQuery, $this->literals['ArrayOperator'][$nodeName]['between']);
        $strQuery = $strQuery . $this->literals['ArrayOperator'][$nodeName]['after'];
        return $strQuery;
    }

    /**
     * Return value from Glob
     *
     * I have no idea why, but Xiag\Rql\Parser\DataType\Glob
     * have not method getValue(). We fix it/
     *
     * @see Xiag\Rql\Parser\DataType\Glob
     * @param Glob $globNode
     * @return string
     */
    public function getValueFromGlob(Glob $globNode)
    {
        $reflection = new \ReflectionClass($globNode);
        $globProperty = $reflection->getProperty('glob');
        $globProperty->setAccessible(true);
        $glob = $globProperty->getValue($globNode);
        $globProperty->setAccessible(false);
        return $glob;
    }

}
