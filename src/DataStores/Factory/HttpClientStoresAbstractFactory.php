<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores\Factory;

use Zend\Db\TableGateway\TableGateway; 
use Interop\Container\ContainerInterface;
use zaboy\res\DataStore\HttpClient;

/**
 * Create and return an instance of the DataStore which based on DbTable
 * 
 * This Factory depends on Container (which should return an 'config' as array)
 *

 * 
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 */
class HttpClientStoresAbstractFactory extends DataStoresAbstractFactoryAbstract
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
        $config = $container->get('config');
        if (!isset($config['dataStore'][$requestedName]['class'])) {
            return false; 
        }
        $requestedClassName = $config['dataStore'][$requestedName]['class'];
        return is_a($requestedClassName, 'zaboy\res\DataStore\HttpClient', true);
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
        $config = $container->get('config');
        $serviceConfig = $config['dataStore'][$requestedName];       
        $requestedClassName = $serviceConfig['class'];
        if (isset($serviceConfig['url'])) {
            $url = $serviceConfig['url'];
        }else{
            throw new DataStoresException( 
                'There is url for ' . $requestedName . 'in config \'dataStore\''
            ); 
        }
        if (isset($serviceConfig['options'])) {
            $options = $serviceConfig['options'];
            return new $requestedClassName($url, $options);
        }else{
            return new $requestedClassName($url);
        }
    }
}    