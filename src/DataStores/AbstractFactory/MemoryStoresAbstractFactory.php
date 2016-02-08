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


/**
 * Create and return an instance of the DataStore which based on DbTable
 * 
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'DataStore' => [
 *
 *     'TheMemoryStore' => [
 *         'class' => 'zaboy\res\DataStore\Memory',
 *     ]
 * ]
 * </code>
 * 
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 */
class MemoryStoresAbstractFactory  extends DataStoresAbstractFactoryAbstract
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
        return is_a($requestedClassName, 'zaboy\res\DataStore\Memory', true);
    }

}    