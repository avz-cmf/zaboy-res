<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use zaboy\res\DataStores\DataStoresException;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * 
 */
abstract class ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            
        ],
        'ArrayOperator' => [
            
        ],
        'ScalarOperator' => [
            'ge' => ['before' => '(' , 'between' => '=>' , 'after' =>')'],
            'gt' => ['before' => '(' , 'between' => '>' , 'after' =>')'],   
            'le' => ['before' => '(' , 'between' => '<=' , 'after' =>')'],
            'lt' => ['before' => '(' , 'between' => '<' , 'after' =>')'], 
        ]
    ];
    
    protected $supportedQueryNodeNames = [];

    protected $emptyCondition;
    
    
    
       
    /**
     * 
     * @param array $options
     */
    public function __construct()
    {
        foreach ($this->literals as $operatorsNames) {
            foreach ($operatorsNames as $operatorName => $placeHolders) {
                $this->supportedQueryNodeNames[] = $operatorName;                
            }
        }

    }
    
    public function prepareFildName($fildName) 
    {
        return $fildName;
    }
    
    public function prepareFildValue($fildValue) 
    {
        if (is_a($fildValue, 'Xiag\Rql\Parser\DataType\Glob', true)) {
            return $this->getValueFromGlob($fildValue);
        }else{
            return $fildValue;    
        }
        
    }
    
    public function getValueFromGlob(Glob $globNode) 
    {
        $reflection = new \ReflectionClass($globNode);
        $globProperty = $reflection->getProperty('glob');
        $globProperty->setAccessible(true);
        $glob = $globProperty->getValue($globNode);
        $globProperty->setAccessible(false);
        return $glob;
    }
    
    /**
     */
    public function __invoke(AbstractQueryNode $rootQueryNode = null) 
    {
        if (isset($rootQueryNode)) {
            return $this->makeAbstractQueryOperator($rootQueryNode);
        }else{
            return $this->emptyCondition;
        }
    }

    public function  makeAbstractQueryOperator( AbstractQueryNode $queryNode) 
    {
        $nodeName = $queryNode->getNodeName();
        if (!in_array($nodeName, $this->supportedQueryNodeNames)) {
                throw new DataStoresException( 
                    'The logical condition not suppoted: ' . $queryNode->getNodeName()
                );  
        }
        switch (true) {
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode', true):
                return $this->makeScalarOperator($queryNode);
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode', true):
                return $this->makeLogicOperator($queryNode);
            case is_a($queryNode, 'Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode', true):
                return $this->makeArrayOperator($queryNode);
            default:
                throw new DataStoresException( 
                    'The logical condition not suppoted: ' . $queryNode->getNodeName()
                ); 
        }
    }

    public function  makeLogicOperator(AbstractLogicOperatorNode  $node) 
    {
        $nodeName = $node->getNodeName();
        $arrayQueries = $node->getQueries();
        $strQuery = $this->literals['LogicOperator'][$nodeName]['before']; 
        foreach ($arrayQueries as $queryNode) {
            /* @var $queryNode AbstractQueryNode*/
            $strQuery = 
                    $strQuery 
                    . $this->makeAbstractQueryOperator($queryNode) 
                    . $this->literals['LogicOperator'][$nodeName]['between']; 
        }
        $strQuery = rtrim($strQuery, $this->literals['LogicOperator'][$nodeName]['between']);
        $strQuery = $strQuery . $this->literals['LogicOperator'][$nodeName]['after'];
        return $strQuery;
    }

    public function  makeScalarOperator(AbstractScalarOperatorNode  $node) 
    {
        $nodeName = $node->getNodeName();
        $value = $node->getValue() instanceof \DateTime ? $node->getValue()->format("Y-m-d") : $node->getValue();       
        $strQuery =
            $this->literals['ScalarOperator'][$nodeName]['before']
            . $this->prepareFildName($node->getField())
            . $this->literals['ScalarOperator'][$nodeName]['between']
            . $this->prepareFildValue($value) 
            . $this->literals['ScalarOperator'][$nodeName]['after'];
        return $strQuery;
    }

    public function  makeArrayOperator(AbstractArrayOperatorNode  $node) 
    {
        $nodeName = $node->getNodeName();
        $arrayValues = $node->getValues();
        
        $strQuery = $this->literals['ArrayOperator'][$nodeName]['before']; 
        foreach ($arrayValues as $value) {
            /* @var $queryNode AbstractQueryNode*/
            $strQuery = 
                    $strQuery 
                    . $this->prepareFildValue($value)
                    . $this->literals['ArrayOperator'][$nodeName]['between']; 
        }
        $strQuery = rtrim($strQuery, $this->literals['ArrayOperator'][$nodeName]['between']);
        $strQuery = $strQuery  . $this->literals['ArrayOperator'][$nodeName]['after'];
        return $strQuery;
    }

}