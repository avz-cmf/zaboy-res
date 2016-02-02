<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStores\DataStoresAbstract;
use zaboy\res\DataStores\DataStoresException;

/**
 * DataStores as array
 * 
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
class Memory extends DataStoresAbstract
{    
    /**
     * Collected items
     * @var array
     */
    protected $_items = array();
    
    /**
     * 
     * @param array $itemsSorce
     */
    public function __construct(array $options = null)
    {
        parent::__construct($options);
        $identifier = $this->getIdentifier();
    }           
            
            
    /**
     * Return Item by id
     * 
     * Method return null if item with that id is absent.
     * Format of Item - Array("id"=>123, "fild1"=value1, ...)
     * 
     * @param int|string|float $id PrimaryKey
     * @return array|null
     */
    public function read($id)
    {
        $this->_checkIdentifierType($id);
        if (isset($this->_items[$id]) ) {
           return $this->_items[$id]; 
        }else{
            return null;
        }
        
    }

    /**
     * 
     * @return array array of keys or empty array
     */
    public function  getKeys() 
    {
        return array_keys($this->_items);
    } 
    
    /**
     * Return items by criteria with mapping, sorting and paging
     * 
     * Example:
     * <code>
     * find(
     *    array('fild2' => 2, 'fild5' => 'something'), // 'fild2' === 2 && 'fild5 === 'something' 
     *    array(self::DEF_ID), // return only identifiers
     *    array(self::DEF_ID => self::DESC),  // Sorting in reverse order by 'id" fild
     *    10, // not more then 10 items
     *    5 // from 6th items in result set (offset of the first item is 0)
     * ) 
     * </code>
     * 
     * ORDER
     * http://www.simplecoding.org/sortirovka-v-mysql-neskolko-redko-ispolzuemyx-vozmozhnostej.html
     * http://ru.php.net/manual/ru/function.usort.php
     * 
     * @see ASC
     * @see DESC
     * @param Array|null $where   
     * @param array|null $filds What filds will be included in result set. All by default 
     * @param array|null $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array    Empty array or array of arrays
     */
    public function find(
        $where = null,             
        $filds = null, 
        $order = null,            
        $limit = null, 
        $offset = null 
    ) {
        $resultArray = array();
        //*********************** $where ***********************
        if ( isset($where) ) {
        $whereBody = "";
            foreach ($where as $fild => $value) {
                if('null'=== strtolower($value )){
                    $whereCheck = "!isset(\$item['$fild'])";
                }elseif('not_null'=== strtolower($value )){
                    $whereCheck = "isset(\$item['$fild'])";
                }else{
                    $whereCheck = 
                        "isset(\$item['$fild']) && "
                        . "\$item['$fild'] == '$value'"
                    ;                
                }
                $whereBody = 
                     $whereBody . '&& ' 
                    . '( ' .  $whereCheck . ' )' . PHP_EOL
                ;
            }
            $whereFunctionBody = PHP_EOL  .
                '$result = ' . PHP_EOL 
                . substr($whereBody, 2) . ';' . PHP_EOL 
                . 'return $result;'
            ;
            
            $whereFunction = create_function('$item', $whereFunctionBody);
            
            foreach ($this->_items as $item) {
                if($whereFunction($item)) {
                    $resultArray[] = $item;
                }
            }
        }else{
            $resultArray = $this->_items;
        }
        
        // ***********************   order   ***********************        
        if (!empty($order)) {
            $nextCompareLevel ='';
            foreach ($order as $ordKey => $ordVal) {
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
            usort($resultArray, $sortFunction);
        }
        
        // *********************  limit, offset   *********************** 
        if ( isset($limit) || isset($offset) ) {
            $resultArrayTemp = $resultArray;
            if ( isset($limit) && isset($offset) ) { 
                $resultArray = array_slice ($resultArrayTemp, $offset, $limit);
            }elseif(isset($limit)){
                $resultArray = array_slice ($resultArrayTemp, 0, $limit);
            }else{
                $resultArray = array_slice ($resultArrayTemp, $offset);
            }   
        }

        // *********************  $filds   ***********************
        $resultArray = $this->selectFilds($resultArray, $filds);
        
        // ***********************   return   *********************** 
        return $resultArray;
    } 
    
    /**
     * By default, insert new (by create) Item. 
     * 
     * It can't overwrite existing item by default. 
     * You can get item "id" for creatad item us result this function.
     * 
     * If  $item["id"] !== null, item set with that id. 
     * If item with same id already exist - method will throw exception, 
     * but if $rewriteIfExist = true item will be rewrited.<br>
     * 
     * If $item["id"] is not set or $item["id"]===null, 
     * item will be insert with autoincrement PrimryKey.<br>
     * 
     * @param array $itemData associated array with or without PrimaryKey
     * @return mix  "id" for creatad item
     */
    public function create($itemData, $rewriteIfExist = false) {
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            $this->_items[] = $itemData;
            $itemsKeys = array_keys($this->_items);
            $id = array_pop($itemsKeys);
            $this->_items[$id] = array_merge(array($identifier => $id), $itemData);
        }elseif(!$rewriteIfExist && isset($this->_items[$itemData[$identifier]])) {
            throw new DataStoresException('Item is already exist with "id" =  ' . $itemData[$identifier]);  
        }else{
            $id = $itemData[$identifier];
            $this->_checkIdentifierType($id);
            $this->_items[$id] = array_merge(array($identifier => $id), $itemData);            
        }
        return $id;
    }

    /**
     * By default, update existing Item.
     * 
     * If item with PrimaryKey == $item["id"] is existing in store, item will updete.
     * Filds wich don't present in $item will not change in item in store.<br>
     * Method will return updated item<br>
     * <br>
     * If $item["id"] isn't set - method will throw exception.<br>
     * <br>
     * If item with PrimaryKey == $item["id"] is absent - method  will throw exception,<br>
     * but if $createIfAbsent = true item will be created and method return inserted item<br>
     * <br>
     * 
     * @param array $itemData associated array with PrimaryKey
     * @return array updated item or inserted item
     */
    public function update($itemData, $createIfAbsent = false) {
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            throw new DataStoresException('Item must has primary key'); 
        }
        $id = $itemData[$identifier];
        $this->_checkIdentifierType($id);

        switch (true) {
             case !isset($this->_items[$id]) && !$createIfAbsent:
                $errorMsg = 'Cann\'t update item with "id" = ' . $id; 
                throw new DataStoresException($errorMsg);
            case !isset($this->_items[$id]) && $createIfAbsent:
                $this->_items[$id] = array_merge(array($identifier => $id), $itemData);        
                break;
            case isset($this->_items[$id]):
                unset($itemData[$id]);
                $this->_items[$id] = array_merge($this->_items[$id], $itemData);  
                break;
        }
        return $this->_items[$id];
    }
    
     /**
      * Delete Item by id. Method do nothing if item with that id is absent.
      * 
      * @param int|string $id PrimaryKey
      * @return int number of deleted items: 0 or 1
      */
    public function delete($id) {
        $this->_checkIdentifierType($id);       
        if ( isset($this->_items[$id]) ){
            unset($this->_items[$id]);
            $deletedItemsCount = 1;
        }else{
            $deletedItemsCount = 0;
        }
        return $deletedItemsCount;
    }  
    
     /**
      * Delete all Items.
      * 
      * @return int number of deleted items or null if object doesn't support it
      */
    public function deleteAll() {
        $deletedItemsCount = count($this->_items);
        $this->_items = array();
        return $deletedItemsCount;
    }
    
//** Interface "Coutable" **                                    **                          **
    
    /**
     * @see coutable
     * @return int
     */
    public function count() {
        return count($this->_items);
    }
    
}