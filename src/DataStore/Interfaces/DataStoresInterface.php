<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\Interfaces;

use zaboy\res\DataStore\Interfaces\ReadInterface;

/**
 * Full Interface for DataStores
 * 
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
interface DataStoresInterface extends ReadInterface
{    
    /**
     * By default, insert new (by create) Item. 
     * 
     * It can't overwrite existing item by default. 
     * You can get creatad item us result this function.
     * 
     * If  $itemData["id"] !== null, item set with that 'id'. 
     * If item with same 'id' already exist - method will throw exception, 
     * but if $rewriteIfExist = true item will be rewrited.<br>
     * 
     * If $itemData["id"] is not set or $itemData["id"]===null, 
     * item will be insert with autoincrement PrimryKey.<br>
     * 
     * @param array $itemData associated array with or without PrimaryKey ["id" => 1, "fild name" = "foo" ]
     * @param bool $rewriteIfExist can item be rewrited if same 'id' exist
     * @return array created item or method will throw exception 
     */
    public function create($itemData, $rewriteIfExist = false);
    
    /**
     * By default, update existing Item.
     * 
     * If item with PrimaryKey == $itemData["id"] is existing in the store, item will update.
     * A filds wich don't present in $itemData will  not be changed in item in the store.<br>
     * This method return updated item<br>
     * <br>
     * If $item["id"] isn't set - the method will throw exception.<br>
     * <br>
     * If item with PrimaryKey == $itemData["id"] is absent in the store - method  will throw exception,<br>
     * but if $createIfAbsent = true item will be created and this method return inserted item<br>
     * <br>
     * 
     * @param array $itemData associated array with PrimaryKey ["id" => 1, "fild name" = "foo" ]
     * @param bool $createIfAbsent can item be created if same 'id' is absent in the store
     * @return array updated or inserted item. 
     */
    public function update($itemData, $createIfAbsent = false);
    
     /**
      * Delete Item by 'id'. Method do nothing if item with that id is absent.
      * 
      * @param int|string $id PrimaryKey
      * @return int number of deleted items: 0 , 1 or null if object doesn't support it
      */
    public function delete($id); 
    
     /**
      * Delete all Items.
      * 
      * @return int number of deleted items or null if object doesn't support it
      */
    public function deleteAll();    
}