<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use zaboy\res\DataStores\DataStoresInterface;
use zaboy\res\DataStores\DataStoresException;
use zaboy\res\DataStores\Read\DataStoreIterator;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node;
use Xiag\Rql\Parser\Node\AbstractQueryNode;
use Xiag\Rql\Parser\Node\SortNode;
use Xiag\Rql\Parser\Node\LimitNode;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Node\Query\LogicOperator;


/**
 * Abstract class for DataStores
 * 
 * @todo make support null in eq(fildname, null) and ne(fildname, null)
 * @todo JsonSerializable https://github.com/zendframework/zend-diactoros/blob/master/doc/book/custom-responses.md#json-responses
 * @todo Adapter paras to config for tests
 * @todo Excel client
 * @todo SVC Store
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
abstract class DataStoresAbstract implements DataStoresInterface 
{   
    /**
     * @see http://php.net/manual/en/function.gettype.php
     */
    const INT_TYPE    = "integer" ;
    const FLOAT_TYPE = "double"; // (for historical reasons "double" is returned in case of a float, and not simply "float")  ;
    const STR_TYPE  = "string" ;   
    
    const LIMIT_INFINITY = 2147483647;
    
    /**
     *
     * @var \zaboy\res\DataStores\ConditionBuilderAbstract
     */
    protected $_conditionBuilder;    
    
    /**
     * 
     * @param array $options
     */
    public function __construct(array $options = null)
    {
    }
    
//** Interface "Zaboy_DataStores_Read_Interface" **            **                          **

    /**
     * Return primary key
     * 
     * Return "id" by default
     * 
     * @see DEF_ID
     * @return string "id" by default
     */
    public function getIdentifier() 
    {
        return self::DEF_ID;
    }    
    
    /**
     * Return Item by id
     * 
     * Method return null if item with that id is absent.
     * Format of Item - Array("id"=>123, "fild1"=value1, ...)
     * 
     * @param int|string $id PrimaryKey
     * @return array|null
     */
    public function read($id)
    {
        $identifier = $this->getIdentifier();
        $this->_checkIdentifierType($id);
        $query = new Query();
        $eqNode = new EqNode($identifier, $id);
        $query->setQuery($eqNode);  
        $queryArray = $this->query($query);
        if (empty($queryArray)) {
            return null;
        } else {
            return $queryArray[0];
        }
    }
    
    /**
     * Return true if item with that id is present.
     * 
     * @param int|string $id PrimaryKey
     * @return bool
     */
    public function has($id) 
    {
        return !(is_null($this->read($id)));
    }
    
    
// ** Interface "Zaboy_DataStores_Write_Interface" **            **                          **
    /**
     * By default, insert new (by create) Item. 
     * 
     * It can't overwrite existing item by default. 
     * You can get creatad item us result this function.
     * 
     * If  $item["id"] !== null, item set with that id. 
     * If item with same id already exist - method will throw exception, 
     * but if $rewriteIfExist = true item will be rewrited.<br>
     * 
     * If $item["id"] is not set or $item["id"]===null, 
     * item will be insert with autoincrement PrimryKey.<br>
     * 
     * @param array $itemData associated array with or without PrimaryKey
     * @return array created item or method will throw exception 
     */
    public function create($itemData, $rewriteIfExist = false) {
        
    }
    
    /**
     * By default, update existing Item with PrimaryKey = $item["id"].
     * 
     * If item with PrimaryKey == $item["id"] is existing in store, item will updete.
     * Filds wich don't present in $item will not change in item in store.
     * Method will return 1<br>
     * 
     * If $item["id"] isn't set - method will throw exception.
     * If item with PrimaryKey == $item["id"] is absent - method do nothing and return 0,
     * but if $createIfAbsent = true item will be created and method return 1.<br>
     * 
     * 
     * @param array $itemData associated array with PrimaryKey
     * @return int number of updeted (created) items: 0 or 1
     */
    public function update($itemData, $createIfAbsent = false) {
        
    }
    
     /**
      * Delete Item by id. Method do nothing if item with that id is absent.
      * 
      * @param int|string $id PrimaryKey
      * @return int number of deleted items: 0 or 1
      */
    public function delete($id) {
        
    }    
    
     /**
      * Delete all Items.
      * 
      * @return int number of deleted items or null if object doesn't support it
      */
    public function deleteAll() {
        $keys = $this->getKeys();
        $numberOfDeletedItems = 0;
        
        
        foreach ($keys as $id) {
            $deletedNumber = $this->delete($id);
            $numberOfDeletedItems = $numberOfDeletedItems + $deletedNumber;
        }
        return $numberOfDeletedItems;
    }
    
    
    /**
     * Interface "Coutable"
     * 
     * @see coutable
     * @return int
     */
    public function count() {
        $keys = $this->getKeys();
        return count($keys);
    }
    
    /**
     * Iterator for Interface IteratorAggregate 
     * 
     * @see IteratorAggregate
     * @return Traversable 
     */
    public function getIterator() {
        return new DataStoreIterator($this);
    }
    
    
    /**
     * Throw Exception if type of Identifier is wrong
     * 
     * @param mix $id
     */
    protected function _checkIdentifierType($id)
    {
        $idType = gettype($id);
        if ($idType == self::INT_TYPE || $idType == self::STR_TYPE || $idType == self::FLOAT_TYPE) {
            return;
        }else{
            throw new DataStoresException(
                    "Type of Identifier is wrong"
            );
        }
    }
    
    
    /**
     * Return items by criteria with mapping, sorting and paging
     * 
     * Example:
     * <code>
     *  $query = new \Xiag\Rql\Parser\Query();
     *  $eqNode = new \Xiag\Rql\Parser\Node\ScalarOperator\EqNode(
     *      'fString', 'val2'
     *  );
     *  $query->setQuery($eqNode);            
     *  $sortNode = new \Xiag\Rql\Parser\Node\Node\SortNode(['id' => '1']);
     *  $query->setSort($sortNode);  
     *  $selectNode = new \Xiag\Rql\Parser\Node\Node\SelectNode(['fFloat']);
     *  $query->setSelect($selectNode);  
     *  $limitNode = new \Xiag\Rql\Parser\Node\Node\LimitNode(2, 1);
     *  $query->setLimit($limitNode);
     *  $queryArray = $this->object->query($query); 
     * </code>
     * 
     * 
     * ORDER
     * http://www.simplecoding.org/sortirovka-v-mysql-neskolko-redko-ispolzuemyx-vozmozhnostej.html
     * http://ru.php.net/manual/ru/function.usort.php
     * 
     * @param Query $query
     */
    public function query(Query $query) 
    {
        $limits = $query->getLimit();
        $limit = !$limits ? 'self::LIMIT_INFINITY' : $query->getLimit()->getLimit();
        $offset =  !$limits ? 0 : $query->getLimit()->getOffset();
        $sort = $query->getSort();
        $sortFilds = !$sort ? [] : $sort->getFields();
        $select = $query->getSelect();
        $selectFilds = !$select ? [] : $select->getFields();
        if (isset($limits) && isset($sort)) {
                $data = $this->doQueryWhere($this, $query, 'self::LIMIT_INFINITY', 0); 
                $sortedData = $this->sortQueryResult($data, $sortFilds);
                $result = array_slice($sortedData, $offset, $limit=='self::LIMIT_INFINITY'?null:$limit);
        }else{
                $data = $this->doQueryWhere($this, $query, $limit, $offset);
                $result = $this->sortQueryResult($data, $sortFilds);
                
        }
        return $this->selectFilds($result, $selectFilds);
    }
    
    /**
     * 
     * @param \Traversable $data
     * @param type $sort
     * @throws DataStoresException
     */
    protected function sortQueryResult($data, $sort)
    {
        if (empty($sort)) {
            return $data;
        }
        $nextCompareLevel ='';
        foreach ($sort as $ordKey => $ordVal) {
            if((int) $ordVal === self::SORT_ASC){
                $cond = '>'; $notCond = '<';
            }elseIf((int) $ordVal === self::SORT_DESC){
                $cond = '<'; $notCond = '>';
            }else{
                throw new DataStoresException('Invalid condition: ' . $ordVal);    
            }
            $prevCompareLevel = 
                "if (\$a['$ordKey'] $cond \$b['$ordKey']) {return 1;};" . PHP_EOL 
                . "if (\$a['$ordKey'] $notCond  \$b['$ordKey']) {return -1;};" . PHP_EOL
            ;
            $nextCompareLevel =$nextCompareLevel . $prevCompareLevel;                 
        }
        $sortFunctionBody = $nextCompareLevel . 'return 0;';
        $sortFunction = create_function('$a,$b', $sortFunctionBody);
        usort($data, $sortFunction);
        return $data;
    }        
    
    protected function selectFilds($data, $filds)
    {
        if (empty($filds)) {
            return $data; 
        }else{
            $resultArray = array();
            foreach($data as $item) {
                $resultArray[] = array_intersect_key($item, array_flip($filds));
            }
            return $resultArray;        
        }    
    }

    protected function doQueryWhere($data, Query $query, $limit, $offset)
    {
        $rootQueryNode = $query->getQuery();
        /* @var $rootQueryNode AbstractQueryNode */
        $conditioon = $this->getQueryWhereConditioon($rootQueryNode);
        $whereFunction = $this->getQueryWhereFunction($conditioon);
        $i = 0;
            $result = [];        
        foreach ($data as $value) {
            switch (true) {
                case !($whereFunction($value)): 
                    // skip!
                    break;           
                case $i < $offset:
                    // increment!
                    $i = $i +1;
                    break;      
                case $limit <> 'self::LIMIT_INFINITY' && $i >= ($limit + $offset): 
                    //enough!
                    return $result;             
                default:
                    // write!
                    $result[] = $value;
                    $i = $i +1;
            }
        }
        return $result;
    }
    
    protected function getQueryWhereConditioon(AbstractQueryNode $queryNode = null)
    {
        $conditionBuilder = $this->_conditionBuilder;
        return $conditionBuilder($queryNode);
    }  
   
    
    protected function getQueryWhereFunction($conditioon)
    {
        $whereFunctionBody = PHP_EOL  .
            '$result = ' . PHP_EOL 
            . rtrim($conditioon, PHP_EOL) . ';' . PHP_EOL 
            . 'return $result;'
        ;
        $whereFunction = create_function('$item', $whereFunctionBody);
        return $whereFunction;
    }
    
    /**
     * 
     * @return array array of keys or empty array
     */
    protected function  getKeys() 
    {
        $identifier = $this->getIdentifier();
        $query = new Query();
        $selectNode = new Node\SelectNode([$identifier]);
        $query->setSelect($selectNode);  
        $queryArray = $this->query($query);
        $keysArray =[];
        foreach ($queryArray as $row) {
            $keysArray[] = $row[$identifier];
        }
        return $keysArray;
    }  
}