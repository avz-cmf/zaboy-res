<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\TableGateway\Factory;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Metadata\Metadata;
use Interop\Container\ContainerInterface;
use zaboy\res\DataStore\Factory\DataStoresAbstractFactoryAbstract;

/**
 * Create and return an instance of the TableGateway
 *
 * Return TableGateway if table with name $requestedName
 * present in database
 *
 * Requre service with name 'db' - db adapter
 *
 * @category   DataStores
 * @package    DataStores
 * @uses zend-db
 * @see https://github.com/zendframework/zend-db
 */
class TableGatewayAbstractFactory extends DataStoresAbstractFactoryAbstract
{
    /*
     * @var array cache of tables names in db
     */

    protected $tableNames;

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
        if (!isset($this->tableNames)) {
            $db = $container->has('db') ? $container->get('db') : null;
            if (isset($db)) {
                $dbMetadata = new Metadata($db);
                $this->tableNames = $dbMetadata->getTableNames();
            } else {
                $this->tableNames = false;
            }
        }
        //is there table with same name?
        if (
                is_array($this->tableNames) && in_array($requestedName, $this->tableNames, true)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create and return an instance of the TableGateway.
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
        $db = $container->get('db');
        $tableGateway = new TableGateway($requestedName, $db);
        return $tableGateway;
    }

}
