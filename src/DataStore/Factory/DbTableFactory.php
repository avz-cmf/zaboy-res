<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStore\DbTableFactoryTrait;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Create and return an instance of the DataStore which based on DbTable
 * 
 * The configuration can contain:
 * <code>
 *  'db' => [
 *	'driver' => 'Pdo_Mysql',
 *	'host' => 'localhost',
 *	'database' => '',
 *  ]
 *  
 * 
 * 'DataStore' => [

 *     'DbTable' => [
 * 
 *         'driver' => 'Pdo_Mysql',
 *         'database' => 'mydatabase',
 *         'tableName' => 'mytableName',
 *         'username' => 'root',
 *         'password' => 'mypassword'
 *     ]
 * ]
 * </code>
 * 
 * If Container is not provided, will use 
 * .././../congig/autoload/dataStore.local.php 
 * (BaseURL//congig/autoload/dataStore.local.php)
 * 
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 */
class DbTableFactory implements FactoryInterface
{

    use DbTableFactoryTrait;
    
    /**
     * Create and return an instance of the DataStore.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return DataStores\DataStoresInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    { 
        return $this->makeDbTableDataStore($container);
    }

}    