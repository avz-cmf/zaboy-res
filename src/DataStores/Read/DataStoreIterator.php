<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Read;

use zaboy\res\DataStores\Read\ReadInterface;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node;

/**
 * Outer iterator for zaboy\res\DataStores\Read\ReadInterface objects
 * 
 * @todo rewrite Next with query()
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
        $identifier = $this->dataStore->getIdentifier();
        $query = new Query();
        $selectNode = new Node\SelectNode([$identifier]);            
        $query->setSelect($selectNode);
        $sortNode = new Node\SortNode([$identifier => 1]);
        $query->setSort($sortNode);  
        $limitNode = new Node\LimitNode(1, 0);
        $query->setLimit($limitNode);
        $queryArray = $this->dataStore->query($query);
        $this->index = $queryArray[0][$identifier];
        $this->index = $queryArray === [] ? null : $queryArray[0][$identifier];
    }

    /**
     * @see Iterator
     * @return array
     */
    public function current()
    {
        $result = isset($this->index) ? $this->dataStore->read($this->index) : null;
        return $result;
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
        $identifier = $this->dataStore->getIdentifier();
        $query = new Query();
        $selectNode = new Node\SelectNode([$identifier]);            
        $query->setSelect($selectNode);
        $sortNode = new Node\SortNode([$identifier => 1]);
        $query->setSort($sortNode);  
        $limitNode = new Node\LimitNode(1, 0);
        $query->setLimit($limitNode);
        $gtNode = new ScalarOperator\GtNode( $identifier, $this->index );
        $query->setQuery($gtNode);
        $queryArray = $this->dataStore->query($query);
        $this->index = $queryArray === [] ? null : $queryArray[0][$identifier];
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