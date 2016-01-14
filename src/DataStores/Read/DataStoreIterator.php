<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Read;

use zaboy\res\DataStores\Read\ReadInterface;

/**
 * Outer iterator for zaboy\res\DataStores\Read\ReadInterface objects
 * 
 * @category   DataStores
 * @package    DataStores
 */
class DataStoreIterator implements \iterator
{  
    /**
     * pointer for current item in iteration
     * 
     * @see Iterator
     * @var mix $index
     */   
    protected $index = null;   
    
    /**
     * array with keys, wich was iteratad in this cycle
     * 
     * @var array $usedKeys
     */   
    protected $usedKeys= array();   
    
    /**
     * @var ReadInterface $dataStores
     */
    protected $dataStore;
    
    /**
     * 
     * @param ReadInterface $dataStores
     */
    public function __construct(ReadInterface $dataStore)
    {
        $this->dataStore = $dataStore;
    }  
   
    /**
     * @see Iterator
     * @return void
     */
    public function rewind()
    {
  
        $this->usedKeys = array();
        $keys = $this->dataStore->getKeys();
        if (empty($keys)) {
            $this->index =  null;
            return null;
        }else{
            asort($keys);
            $this->index =  array_shift($keys);
            $this->usedKeys[] = $this->index; 
            return $this->index;
        }

    }

    /**
     * @see Iterator
     * @return array
     */
    public function current()
    {
        if (isset($this->index) && ($this->dataStore->read($this->index) !== null)) {
            return $this->dataStore->read($this->index);
        }else{
            return null;
        } 
    }

    /**
     * @see Iterator
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * @see Iterator
     * @return array
     */
    public function next()
    {
        $this->usedKeys[] = $this->index;       
        $keys = $this->dataStore->getKeys();
        if ( empty($keys)) {
            $this->usedKeys = array();
            return null;
        }else{
            asort($keys);
            foreach ($keys as $id) {
                if (!in_array($id, $this->usedKeys)) {
             
                    $this->index = $id;
                    return $this->dataStore->read($this->index);
                }
            }
            $this->index = null;
            return null;
        }
    }

    /**
     * @see Iterator
     * @return bool
     */
    public function valid()
    {
        return isset($this->index) && ($this->dataStore->read($this->index) !== null);
    }
}