<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Middleware;

use zaboy\res\DataStores\DataStoresAbstract;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use zaboy\res\Middlewares\StoreMiddlewareAbstract;
use zaboy\res\DataStore\Memory;

/**
 * Middleware which contane Memory Store
 * 
 * Add Memory store to Request
 * 
 * @category   DataStores
 * @package    DataStores
 */
class MiddlewareMemoryStore extends StoreMiddlewareAbstract
{
    public function __construct(Memory $dataStore = null)
    {
        if (empty($dataStore)) {
            $dataStore = new Memory();
        }
        parent::__construct($dataStore);
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $request = $request->withAttribute('memoryStore', $this->dataStore);

        if ($next) {
            return $next($request, $response);
        }
        return $response;
    }        
}