<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Middlewares;

use zaboy\res\DataStores\DataStoresAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\MiddlewareInterface;

/**
 * Middleware which contane DataStore
 *
 * @category   DataStores
 * @package    DataStores
 */
abstract class StoreMiddlewareAbstract implements MiddlewareInterface
{

    /**
     *
     * @var \DataStores\Interfaces\DataStoresInterface
     */
    protected $dataStore;

    /**
     *
     * @param DataStoresAbstract $dataStore
     */
    public function __construct(DataStoresAbstract $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    abstract public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null);
}
