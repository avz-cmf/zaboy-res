<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Read;

/**
 * Read Interface for DataStores
 * 
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
interface ReadInterface
{    

    /**
     * Default identifier
     * 
     * @see getIdentifier()
     */
    const DEF_ID = 'id';
    
    /**
     * sorting by ascending
     */
    const ASC = 'ASC';
    
    /**
     * sorting by descending
     */
    const DESC = 'DESC'; 
    
    /**
     * sorting by ascending from Xiag\Rql\Parser\Query;
     */
    const SORT_ASC = 1;
    
    /**
     * sorting by descending from Xiag\Rql\Parser\Query;
     */
    const SORT_DESC = -1;
    
    /**
     * Return primary key
     * 
     * Return "id" by default
     * 
     * @see DEF_ID
     * @return string "id" by default
     */
    public function getIdentifier();    
    
    
    /**
     * Return Item by id
     * 
     * Method return null if item with that id is absent.
     * Format of Item - Array("id"=>123, "fild1"=value1, ...)
     * 
     * @param int|string $id PrimaryKey
     * @return array|null
     */
    public function read($id);
    
    /**
     * Return true if item with that id is present.
     * 
     * @param int|string $id PrimaryKey
     * @return bool
     */
    public function has($id);   
    
    /**
     * 
     * @return array array of keys or empty array
     */
    public function  getKeys();        
    
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
    );

}