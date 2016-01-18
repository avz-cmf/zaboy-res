<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Db\Adapter;

use Zend\Db\Adapter\Adapter;
use Interop\Container\ContainerInterface;


/**
 * DataStores as Db Table
 * 
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 * @see http://en.wikipedia.org/wiki/Create,_read,_update_and_delete 
 */
class AdapterFactory
{  
    
    /**
     * Create and return an instance of the Adapter.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return Zend\Db\Adapter\Adapter
     */
    public function __invoke(ContainerInterface $container, $requestedName) 
    {
        $config = $container->get('config');
        return new Adapter($config['db']);
    }                

}