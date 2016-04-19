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
use zaboy\res\DataStore\ConditionBuilder\PhpConditionBuilder;
use Xiag\Rql\Parser\Node\AbstractQueryNode;

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
    public function __construct(array $options = null, ConditionBuilderAbstract $conditionBuilder = null)
    {
        parent::__construct($options);
        if ( isset($conditionBuilder)) {
            $this->_conditionBuilder = $conditionBuilder;
        }  else {
            $this->_conditionBuilder = new PhpConditionBuilder;
        }
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
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            $this->_items[] = $itemData;
            $itemsKeys = array_keys($this->_items);
            $id = array_pop($itemsKeys);
        }elseif(!$rewriteIfExist && isset($this->_items[$itemData[$identifier]])) {
            throw new DataStoresException('Item is already exist with "id" =  ' . $itemData[$identifier]);  
        }else{
            $id = $itemData[$identifier];
            $this->_checkIdentifierType($id);
            $this->_items[$id] = array_merge(array($identifier => $id), $itemData);            
        }
        $this->_items[$id] = array_merge(array($identifier => $id), $itemData);
        return $this->_items[$id];
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
    
    /**
     * Iterator for Interface IteratorAggregate 
     * 
     * @see IteratorAggregate
     * @return Traversable 
     */
    public function getIterator() {
        return new \ArrayIterator($this->_items);
    }

    /**
     * 
     * @return array array of keys or empty array
     */
    protected function  getKeys() 
    {
        return array_keys($this->_items);
    } 
}