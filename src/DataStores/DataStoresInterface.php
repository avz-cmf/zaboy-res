<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use \IteratorAggregate;
use \Countable;
use zaboy\res\DataStores\Read\ReadInterface;
use zaboy\res\DataStores\Write\WriteInterface;

/**
 * ReadWrite Interface for DataStores
 * 
 * @category   DataStores
 * @package    DataStores
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
interface DataStoresInterface extends ReadInterface, WriteInterface, IteratorAggregate, Countable
{    
 
}