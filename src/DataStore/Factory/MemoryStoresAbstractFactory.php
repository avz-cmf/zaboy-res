<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\Factory;

use Interop\Container\ContainerInterface;

/**
 * Create and return an instance of the array in Memory
 *
 * This Factory depends on Container (which should return an 'config' as array)
 *
 * The configuration can contain:
 * <code>
 * 'DataStore' => [
 *     'TheMemoryStore' => [
 *         'class' => 'zaboy\res\DataStore\Memory',
 *     ]
 * ]
 * </code>
 *
 * @category   DataStores
 * @package    DataStores
 */
class MemoryStoresAbstractFactory extends DataStoresAbstractFactoryAbstract
{

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
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
