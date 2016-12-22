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
use Psr\Log\LogLevel;
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

    /**
     * @dataProvider provideLogDateTime
     * @param $dateTime
     * @param $expectedTime
     */
    public function testLog_withTime($dateTime, $expectedTime)
    {
        $this->object = $this->getLogger();
        $this->object->log(LogLevel::ERROR,
            $dateTime . "|" . "Error message of level emergency with context: {user}",
            ['user' => 'Bob']);
        $expected = [
            $expectedTime . ' ' . LogLevel::ERROR . ' ' .
            'Error message of level emergency with context: Bob'
        ];
    }

    public function provideLogDateTime()
    {
        $time = new \DateTime();
        return [
            [$time->format('Y-m-d H:i:s'), $time->getTimestamp()],
            [$time->format('D M j G:i:s T Y'), $time->getTimestamp()],
            [$time->getTimestamp(), $time->getTimestamp()],
        ];
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

    public function getLogsWithTime()
    {
        $logs = [];
        $data = $this->dataStore->query(new Query());
        foreach ($data as $item) {
            $logs[] = $item['time'] . " " . $item['level'] . " " . $item['message'];
        }
        return $logs;
    }
}
