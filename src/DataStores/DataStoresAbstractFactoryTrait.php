<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStores;

use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;
use zaboy\res\DataStore\DbTableFactoryTrait;
use zaboy\res\DataStore\Memory;


trait  DataStoresAbstractFactoryTrait
{
    use DbTableFactoryTrait;
    
    /**
     * Determine if we can create a service with name
     * 
     * @param \zaboy\res\DataStores\ServiceLocatorInterface $serviceLocator
     * @param type $name
     * @param type $requestedName
     * @return type
     */
    public function canMakeDataStore(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        if (!isset($config['dataStore'][$requestedName]['class'])) {
            return false; 
        }
        $requestedClassName = $config['dataStore'][$requestedName]['class'];
        $methodName = 'get' . $this->getTwoLastWordFromClass($requestedClassName);
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
    public function makeDataStore(ContainerInterface $container, $requestedName)
    {
        $config = $container->get('config');
        $requestedClassName = $config['dataStore'][$requestedName]['class'];
        $methodName = 'get' . $this->getTwoLastWordFromClass($requestedClassName);
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
            $tableName = $this->config['tableName'];
        }else{
            throw new DataStoresException( 
                'There is not table name for ' . $requestedName . 'in config \'dataStore\''
            ); 
        }             
        return $this->makeDbTableDataStore($container, $tableName);

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

    protected function getTwoLastWordFromClass($className)
    {
        $arrayWords = explode('\\', $className);
        $wordFirst = array_pop($arrayWords);
        $wordSecond = array_pop($arrayWords);
        return $wordSecond . $wordFirst;       
    }
                
}
                
                
                
                
                
                
                
                
                
                
