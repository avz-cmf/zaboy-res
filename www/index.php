<?php

use Xiag\Rql\Parser\Node\Query\ScalarOperator;


// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));

// Setup autoloading
require '/vendor/autoload.php';
//$container = include 'config/container.php';

include 'test/src/DataStore/AbstractTest.php';
include 'test/src/DataStore/MemoryTest.php';
use zaboy\test\res\DataStore\MemoryTest;
$test = new MemoryTest();

$test->presetUp();
$test->test_QueryEq();

echo('<!DOCTYPE html><html><head></head><body>');

echo ( '!!!!!!!!!!!!!!' . PHP_EOL . '<br>');



echo('</body></html>');
















/**

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


/* @var $statement Zend\Db\Adapter\DriverStatementInterface */

/**
$statement = $adapter->query('SELECT * FROM '
    . $qi('res_test')
    . ' WHERE id = ' . $fp('val_id'));


$results = $statement->execute(array('val_id' => 3));

$row = $results->current();
$name = $row['notes'];
 */

