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
 * Create and return an instance of the DataStore which based on Http Client
 *
 * The configuration can contain:
 * <code>
 * 'DataStore' => [
 *
 *     'HttpClient' => [
 *         'class' => 'zaboy\res\DataStore\HttpDatastoreClassname',
 *          'url' => 'http://site.com/api/resource-name',
 *          'options' => ['timeout' => 30]
 *     ]
 * ]
 * </code>
 *
 * @category   DataStores
 * @package    DataStores
 */
class HttpClientStoresAbstractFactory extends DataStoresAbstractFactoryAbstract
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
        return is_a($requestedClassName, 'zaboy\res\DataStore\HttpClient', true);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $serviceConfig = $config['dataStore'][$requestedName];
        $requestedClassName = $serviceConfig['class'];
        if (isset($serviceConfig['url'])) {
            $url = $serviceConfig['url'];
        } else {
            throw new DataStoresException(
            'There is not url for ' . $requestedName . 'in config \'dataStore\''
            );
        }
        if (isset($serviceConfig['options'])) {
            $options = $serviceConfig['options'];
            return new $requestedClassName($url, $options);
        } else {
            return new $requestedClassName($url);
        }
    }

}
