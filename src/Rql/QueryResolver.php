<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Rql;

use zaboy\res\DataStores\DataStoresException;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\SortNode;
use Xiag\Rql\Parser\Node\Query\AbstractLogicOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractScalarOperatorNode;
use Xiag\Rql\Parser\Node\Query\AbstractArrayOperatorNode;
use Xiag\Rql\Parser\Token;

/**
 * RqlEncoder 
 * 
 * @category   DataStores
 * @package    DataStores
 */
class QueryResolver
{    
    /**
     * @var string
     */
    protected $rqlQueryString = '';  
    
    /**
     */
    public function  rqlEncode(Query $query) 
    {
        $this->rqlQueryString = '';
        $this
                ->addOperator($this->makeRootQuery($query))
                ->addOperator($this->makeLimit($query))
                ->addOperator($this->makeSort($query))              
                ->addOperator($this->makeSelect($query))
        ;        
        return $this->rqlQueryString;
    }
    
    /**
     * 
     * @param string $operator
     * @return \zaboy\res\Rql\QueryResolver
     */
    public function  addOperator($operator) 
    {
        if ( !empty($operator) ) {
            if ( empty($this->rqlQueryString) ) {
                $this->rqlQueryString = $operator;
            }else{
                $this->rqlQueryString = $this->rqlQueryString . '&' . $operator;
            }   
        }
        return $this;
    }
    
    /**
     */
    public function  makeRootQuery(Query $query) 
    {
        $rootNode = $query->getQuery();
        if (isset($rootNode)) {
            return $this->makeAbstractQueryOperator($rootNode);
        }else{
            return '';
        }
    }

    public function  makeAbstractQueryOperator( AbstractQueryNode $queryNode) 
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
                    'The logical condition not suppoted: ' . $queryNode->getNodeName()
                ); 
        }
    }

    public function  makeLogicOperator(AbstractLogicOperatorNode  $node) 
    {
        $arrayQueries = $node->getQueries();
        $strQuery = $node->getNodeName() . '('; 
        foreach ($arrayQueries as $queryNode) {
            /* @var $queryNode AbstractQueryNode*/
            $strQuery = $strQuery . $this->makeAbstractQueryOperator($queryNode) . ','; 
        }
        $strQuery = rtrim($strQuery, ',');
        $strQuery = $strQuery . ')';
        return $strQuery;
    }

    public function  makeScalarOperator(AbstractScalarOperatorNode  $node) 
    {
        $strQuery = $node->getNodeName() . '(' . $node->getField() . ',' . $node->getValue() . ')';
        return $strQuery;
    }

    public function  makeArrayOperator(AbstractArrayOperatorNode  $node) 
    {
        $arrayValues = $node->getValues();
        $fild = $node->getField();
        $strQuery = $node->getNodeName() . '(' . $fild . '('; 
        foreach ($arrayValues as $value) {
            /* @var $queryNode AbstractQueryNode*/
            $strQuery = $strQuery . $value . ','; 
        }
        $strQuery = rtrim($strQuery, ',');
        $strQuery = $strQuery  . '))';
        return $strQuery;
    }

    public function  makeLimit(Query $query) 
    {
        $objLimit = $query->getLimit();
        $limit = !$objLimit ? 'Infinity' : $objLimit->getLimit();
        $offset =  !$objLimit ? 0 : $objLimit->getOffset();
        if ($limit == 'Infinity' && $offset == 0) {
            return '';     
        }else{
            $strLimit =  sprintf('limit(%s,%s)',$limit, $offset);
            return $strLimit;      
        }  
    }

    public function  makeSort(Query $query) 
    {
        $objSort = $query->getSort();
        $sortFilds = !$objSort ? [] : $objSort->getFields();
        if (empty($sortFilds)) {
            return '';      
        }else{
            $strSelect =  'sort(';
            foreach ($sortFilds as $key => $value) {
                $prefix = $value == SortNode::SORT_DESC ? '-' : '+';
                $strSelect =  $strSelect . $prefix . $key . ',';
            }
            $strSelect = rtrim($strSelect, ',');
            $strSelect =  $strSelect . ')';
            return $strSelect;      
        }  
    }

    public function makeSelect(Query $query) 
    {
        $objSelect = $query->getSelect();  //What filds will return
        $selectFilds = !$objSelect ? [] : $objSelect->getFields();
        if (empty($selectFilds)) {
            return '';   
        }else{
            $strSelect =  'select(' . implode(',', $selectFilds) . ')';
            return $strSelect;      
        }  
    }
}    