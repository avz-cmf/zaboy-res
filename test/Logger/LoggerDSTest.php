<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 19.12.16
 * Time: 11:43 AM
 */

namespace zaboy\test\Logger;


use Interop\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\LoggerInterfaceTest;
use Xiag\Rql\Parser\Query;
use zaboy\res\Di\InsideConstruct;
use zaboy\res\Logger\LoggerDS;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;

class LoggerDSTest extends LoggerInterfaceTest
{
    /** @var  LoggerInterface */
    protected $object;

    /** @var  ContainerInterface */
    protected $container;

    /** @var  DataStoresInterface */
    protected $dataStore;

    public function setUp()
    {
        $container = include './config/container.php';
        $this->container = $container;
        InsideConstruct::setContainer($this->container);
        $this->dataStore = $this->container->has('logDataStore') ? $this->container->get('logDataStore') : null;
        $this->dataStore->deleteAll();
    }

    public function testLog_withTime() {

    }

    public function testLog_withoutTime(){

    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return new LoggerDS();
    }

    /**
     * This must return the log messages in order.
     *
     * The simple formatting of the messages is: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo".
     *
     * @return string[]
     */
    public function getLogs()
    {
        $logs = [];
        $data = $this->dataStore->query(new Query());
        foreach ($data as $item) {
            $logs[] = $item['level'] . " " . $item['message'];
        }
        return $logs;
    }
}
