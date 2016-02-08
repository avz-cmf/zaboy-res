<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Tree;

use zaboy\res\DataStores\DataStoresAbstract;


/**
 * Abstract class for DataStores
 * 
 * @todo JsonSerializable https://github.com/zendframework/zend-diactoros/blob/master/doc/book/custom-responses.md#json-responses
 * @todo Adapter paras to config for tests
 * @todo Excel client
 * @todo SVC Store
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
abstract class DataStoresTreeAbstract implements DataStoresAbstract 
{   

    const ROOT_PARENT  = null;
    const PARENT_IDENTIFIER = "parent";
    
    /**
     * 
     * @param array $options
     */
    public function __construct(DataStoresAbstract $store)
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
        $itemsArray = $this->find(
            array($identifier => $id)
        );
        if (empty($itemsArray)) {
            return null;
        } else {
            return $itemsArray[0];
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
    
    /**
     * 
     * @return array array of keys or empty array
     */
    public function  getKeys() 
    {
        $identifier = $this->getIdentifier();
        $keysArray = $this->find(
            null,
            array($identifier)       
        );
        return $keysArray;
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
     * @todo Make support for null value for sorting and where
     * @see ASC
     * @see DESC
     * @param string|int|Array|null $where   
     * @param array|null $filds What filds will be included in result set. All by default 
     * @param array|null $order
     * @param int|null $limit
     * @param int|null $offset
     * @return array    Empty array or array of arrays
     */
    abstract public function find(
        $where = null,             
        $filds = null, 
        $order = null,            
        $limit = null, 
        $offset = null 
    );
    
// ** Interface "Zaboy_DataStores_Write_Interface" **            **                          **
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
     * @return int|string|null  "id" for creatad item
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
        
    }
    
    
    /**
     * Interface "Coutable"
     * 
     * @see coutable
     * @return int
     */
    public function count() {
        
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
    
    
}