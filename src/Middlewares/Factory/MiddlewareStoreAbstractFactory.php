<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Middlewares\Factory;

//use Zend\ServiceManager\Factory\AbstractFactoryInterface;
//uncomment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\AbstractFactoryInterface;
//comment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stratigility\MiddlewareInterface;
use Interop\Container\ContainerInterface;

/**
 * Factory for middleware which contane DataStore
 *
 * config
 * <code>
 *  'middleware' => [
 *      'MiddlewareName' => [
 *          'class' =>'zaboy\res\MiddlewareType',
 *          'dataStore' => 'zaboy\res\DataStore\Type'
 *      ],
 *      'MiddlewareAnotherName' => [
 *          'class' =>'zaboy\res\MiddlewareAnotherType',
 *          'dataStore' => 'zaboy\res\DataStore\AnotherType'
 *      ],
 *  ...
 *  ],
 * </code>
 * @category   DataStores
 * @package    DataStores
 */
class MiddlewareStoreAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Can the factory create an instance for the service?
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $isClassName = isset($config['middleware'][$requestedName]['class']);
        if ($isClassName) {
            $requestedClassName = $config['middleware'][$requestedName]['class'];
            return is_a($requestedClassName, 'zaboy\res\Middlewares\StoreMiddlewareAbstract', true);
        } else {
            return false;
        }
    }

    /**
     * Create and return an instance of the Middleware.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return MiddlewareInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $serviceConfig = $config['middleware'][$requestedName];
        $requestedClassName = $serviceConfig['class'];
        //take store for Middleware
        $dataStoreServiceName = isset($serviceConfig['dataStore']) ? $serviceConfig['dataStore'] : null;
        if (!($container->get($dataStoreServiceName))) {
            throw new DataStoresException(
            'Can\'t get Store' . $dataStoreServiceName
            . ' for Middleware ' . $requestedName);
        }
        $dataStore = $container->get($dataStoreServiceName);
        return new $requestedClassName($dataStore);
    }

    /**
     * Determine if we can create a service with name
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
