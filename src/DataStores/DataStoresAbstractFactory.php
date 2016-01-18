<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager;
use Interop\Container\ContainerInterface;

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
     * @var array|null
     */
    protected  $config = null;
    
    /*
     * @var Interop\Container\ContainerInterface
     */
    protected $container = null;
    
    /**
     * Can the factory create an instance for the service?
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName) 
    {
        $this->container = $container ?: new ServiceManager();
        $this->config = $this->config ?: $this->container->get('config');
        return isset($this->config['dataStore'][$requestedName]);
    }

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
        $this->container = $this->container ?: new ServiceManager();
        $config = $this->container->has('config') ? $this->container->get('config') : [];
        $this->config = isset($config['dataStore']) ? $config['dataStore']:[];
        if (isset($this->config[$requestedName]['class'])) {
            $className = $this->config[$requestedName]['class'];
        }
        if (isset($this->config[$requestedName]['options'])) {
            $options = $this->config[$requestedName]['options'];
        }
        if (class_exists($className . 'Factory')) {
            $factoryName = $className . 'Factory';            
            $factory = new $factoryName();
            $dataStore = $factory($container, $requestedName, $options);
        } else {
         $dataStore = new  $className($options);           
        }
        return $dataStore;
    }
}    