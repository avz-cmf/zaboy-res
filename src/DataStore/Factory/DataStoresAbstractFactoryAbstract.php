<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\Factory;

//use Zend\ServiceManager\Factory\AbstractFactoryInterface;
//uncomment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\AbstractFactoryInterface;
//comment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

/**
 * Create and return an instance of the DataStore which based on DbTable
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration MUST contain:
 * <code>
 * 'DataStore' => [
 *     'TheStore' => [
 *         'class' => 'zaboy\res\DataStore\ClassName',
 *     ]
 * ]
 * </code>
 *
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 */
abstract class DataStoresAbstractFactoryAbstract implements AbstractFactoryInterface
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
    abstract public function canCreate(ContainerInterface $container, $requestedName);

    /**
     * Create and return an instance of the DataStore.
     *
     * 'use Zend\ServiceManager\AbstractFactoryInterface;' for V2 to
     * 'use Zend\ServiceManager\Factory\AbstractFactoryInterface;' for V3
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return \DataStores\Interfaces\DataStoresInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $serviceConfig = $config['dataStore'][$requestedName];
        $requestedClassName = $serviceConfig['class'];
        return new $requestedClassName();
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
        return $this->canCreate($serviceLocator, $requestedName);
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
        return $this->__invoke($serviceLocator, $requestedName);
    }

}
