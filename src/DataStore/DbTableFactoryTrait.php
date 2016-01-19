<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore;

use zaboy\res\DataStores\DataStoresException;
use Zend\Db\TableGateway\TableGateway;

trait DbTableFactoryTrait
{
    
    /**
     * Return Table Name.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @return DataStores\DataStoresInterface
     */
    protected function getTabeName () {
        return 'db_table';
    }
    
    /**
     * Create and return an instance of the DataStore.
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $tableName
     * @return DataStores\DataStoresInterface
     */
    public function makeDbTableDataStore ($container, $tableName = null) 
    {
        $tabeName = isset($tableName) ? $tableName : $this->getTabeName();
        $db = $container->has('db') ? $container->get('db') : null;
        if (isset($db)) {
            $tableGateway = new TableGateway($tabeName, $db);
        } else {
            throw new DataStoresException( 
                'Can\'t create Zend\Db\TableGateway\TableGateway for ' . $tabeName
            ); 
        }
        $dataStore =  new DbTable($tableGateway);
        return $dataStore;
    }
}    