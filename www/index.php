<?php

use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;

use zaboy\middleware\Middlewares\Factory\RestActionPipeFactory;
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
// Setup autoloading
require '/vendor/autoload.php';
$container = include 'config/container.php';


//include 'test/src/DataStore/AbstractTest.php';
include 'test/src/Middlewares/Factory/MiddlewareStoreAbstractFactoryTest.php';
use zaboy\test\middleware\Middlewares\Factory\MiddlewareStoreAbstractFactoryTest;
$test = new MiddlewareStoreAbstractFactoryTest();
//$test->setUp();
$test->testMiddlewareMemoryStore__invoke();


echo('<!DOCTYPE html><html><head></head><body>');
echo ( '!!!!!!!!!!!!!!' . PHP_EOL . '<br>');
echo('</body></html>');



use zaboy\res\NameSpase;
use Zend\Db;

$adapter = new Db\Adapter\Adapter(
    array(
        'driver' => 'Pdo_Mysql',
        'database' => 'zav_res',
        'username' => 'root',
        'password' => ''
     )
);

$qi = function($name) use ($adapter) { return $adapter->platform->quoteIdentifier($name); };
$fp = function($name) use ($adapter) { return $adapter->driver->formatParameterName($name); };

$statement = $adapter->query('SELECT * FROM '
    . $qi('res_test')
    . ' WHERE id = ' . $fp('val_id'));


$results = $statement->execute(array('val_id' => 3));
$row = $results->current();
$name = $row['notes'];

/**
$app    = new MiddlewarePipe();


// Landing page
$app->pipe('/', function ($req, $res, $next) {
    if (! in_array($req->getUri()->getPath(), ['/', ''], true)) {
        return $next($req, $res);
    }
    return $res->end('Hello world!');
});
$restPipe = RestActionPipeFactory();
// Another page
$app->pipe('/foo', function ($req, $res, $next) {
    return $res->end('FOO!');
});

$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();
*/