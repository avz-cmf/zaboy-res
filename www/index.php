<?php
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));

// Setup autoloading
require '/vendor/autoload.php';


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

/**
/* @var $statement Zend\Db\Adapter\DriverStatementInterface */

/**
$statement = $adapter->query('SELECT * FROM '
    . $qi('res_test')
    . ' WHERE id = ' . $fp('val_id'));


$results = $statement->execute(array('val_id' => 3));

$row = $results->current();
$name = $row['notes'];
 */

echo('<!DOCTYPE html><html><head></head><body>');

echo($name);
echo ( PHP_EOL . '<br>');
echo ( PHP_EOL . '<br>');

/**

$rowset = $table->select(array('id' =>'4'));
echo 'notes: ' . PHP_EOL . '<br>';
foreach ($rowset as $projectRow) {
     echo $projectRow['notes'] . PHP_EOL . '<br>';
}
 */

echo 'updated: ' . PHP_EOL . '<br>';
echo $rowset . PHP_EOL . '<br>';


/**
 * 

 * 
 * $parameterContainer = new \Zend\Db\Adapter\ParameterContainer();
 * $parameterContainer->offsetSet('paramName', $someString, $parameterContainer::TYPE_STRING);
 * 
 * $sql = "SELECT * FROM MyTable WHERE name = :name";
 * $stmt = $pdo->prepare($sql);
 * $stmt->execute( array(":name" => "Bill") ); or $stmt->execute($parameterContainer);
 * 
 * $aa = new NameSpase\NameOfClass();
 * echo($aa->sumAB(1, 2));
 * 
 * 
use zaboy\test\res\DataStore\DbTableTest;
$test = new DbTableTest;

$test->testUpdate_withtIdwhichAbsent_ButCreateIfAbsent_True();
 */
echo('</body></html>');

