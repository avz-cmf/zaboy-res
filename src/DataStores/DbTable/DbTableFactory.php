<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStores\DataStoresException;
use zaboy\res\DataStore\DbTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;
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
    const TABLE_NAME = 'db_table';

    /**
     * @var array|null
     */
    protected  $config;

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
        $config = $container->has('config') ? $container->get('config') : [];
        $this->config = isset($config['dataStore'][$requestedName]) ? $config['dataStore'][$requestedName]:[];
        if (isset($this->config['class'])) {
            $className = $this->config['class'];
        }else{
            $className = 'zaboy\res\DataStore\DbTable';
        }
        $tableName = isset($this->config ['tableName']) ? $this->config ['tableName'] : null;
        $db = $container->has('db') ? $container->get('db') : null;
        if (isset($tableName) && isset($db)) {
            $tableGateway = new TableGateway(self::TABLE_NAME, $db);
        } else {
            throw new DataStoresException( 
                'Can\'t create Zend\Db\TableGateway\TableGateway for ' . self::TABLE_NAME
            ); 
        }
        $dataStore =  new DbTable($tableGateway, $options);
        return $dataStore;
    }

}    