<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\AbstractFactory;

//use Zend\ServiceManager\Factory\AbstractFactoryInterface; 
//uncomment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\AbstractFactoryInterface; 
//comment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\ServiceLocatorInterface; 
use Zend\Db\TableGateway\TableGateway; 
use Interop\Container\ContainerInterface;
use zaboy\res\DataStore\Memory;
use zaboy\res\DataStore\DbTable;


/**
 * Create and return an instance of the DataStore which based on DbTable
 * 
 *  This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'DataStore' => [
 * 	'db' => array(
		'driver' => 'Pdo_Mysql',
		'host' => 'localhost',
		'database' => '',
	)
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
class DataStoresAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Can the factory create an instance for the service?
     * 
     * For Service manager V3
     * Edit 'use' section if need:
     * Change:
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2 to 
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3 
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) 
    {
        return $this->canMakeDataStore($container, $requestedName);
    }

    /**
     * Create and return an instance of the DataStore.
     *
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2 to 
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3 
     * 
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return DataStores\DataStoresInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) 
    {
        return $this->makeDataStore( $container, $requestedName);
    }
    
    /**
     * Determine if we can create a service with name
     * 
     * For Service manager V2
     * Edit 'use' section if need:
     * Change:
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3 to 
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canMakeDataStore($serviceLocator, $requestedName);
    }

    /**
     * Create service with name
     *
     * For Service manager V2
     * Edit 'use' section if need:
     * Change:
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3 to 
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->makeDataStore( $serviceLocator, $requestedName);
    }
    
    /**
     * Determine if we can create a service with name
     * 
     * @param \zaboy\res\DataStores\ServiceLocatorInterface $serviceLocator
     * @param type $name
     * @param type $requestedName
     * @return type
     */
    protected function canMakeDataStore(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config['dataStore'][$requestedName]['class'])) {
            return false; 
        }
        $requestedClassName = $config['dataStore'][$requestedName]['class'];
        $methodName = 'get' . $this->getTwoLastWordsFromClass($requestedClassName);
        return  method_exists($this, $methodName);
    }   
    
    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    protected function makeDataStore(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $requestedClassName = $config['dataStore'][$requestedName]['class'];
        $methodName = 'get' . $this->getTwoLastWordsFromClass($requestedClassName);
        return $this->$methodName($container, $requestedName);
    }
    
        
    /**
     * 
     * @param ContainerInterface $container
     * @param sring $requestedName
     * @return zaboy\res\DataStore\DbTable
     * @throws DataStoresException
     */
    protected function getDataStoreDbTable(ContainerInterface $container, $requestedName)
    {
        $serviceConfig = $container->get('config')['dataStore'][$requestedName];
        if (isset($serviceConfig['tableName'])) {
            $tableName = $serviceConfig['tableName'];
        }else{
            throw new DataStoresException( 
                'There is not table name for ' . $requestedName . 'in config \'dataStore\''
            ); 
        }             
        $db = $container->has('db') ? $container->get('db') : null;
        if (isset($db)) {
            $tableGateway = new TableGateway($tableName, $db);
        } else {
            throw new DataStoresException( 
                'Can\'t create Zend\Db\TableGateway\TableGateway for ' . $tableName
            ); 
        }
        $dataStore =  new DbTable($tableGateway);
        return $dataStore;
    }

    /**
     * 
     * @param ContainerInterface $container
     * @param sring $requestedName
     * @return zaboy\res\DataStore\Memory
     */
    protected function getDataStoreMemory(ContainerInterface $container, $requestedName)
    {
        return new Memory();
    }

    protected function getTwoLastWordsFromClass($className)
    {
        $arrayWords = explode('\\', $className);
        $wordFirst = array_pop($arrayWords);
        $wordSecond = array_pop($arrayWords);
        return $wordSecond . $wordFirst;       
    }
           
}    