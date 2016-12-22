<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 17.12.16
 * Time: 10:23 AM
 */

namespace zaboy\res\Logger;


use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use zaboy\res\Di\InsideConstruct;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;

class LoggerDS extends AbstractLogger
{

    protected $levelEnum = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];
    /** @var  DataStoresInterface */
    protected $logDataStore;

    public function __construct(DataStoresInterface $logDataStore = null)
    {
        InsideConstruct::initServices();
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {

        $replace = [];
        if (!in_array($level, $this->levelEnum)) {
            throw new InvalidArgumentException("Invalid Level");
        }
        foreach ($context as $key => $value) {
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        $split = preg_split('/\|/', strtr($message, $replace), 2, PREG_SPLIT_NO_EMPTY);
        if (count($split) == 2) {
            $id = is_numeric($split[0]) ? $split[0] : (new \DateTime($split[0]))->getTimestamp();
            $message = $split[1];
        } else {
            $id = microtime(true) - date('Z');
            $message = $split[0];
        }

        $this->logDataStore->create([
            'id' =>  uniqid("", true) .'_'. $id,
            'level' => $level,
            'message' => $message
        ]);
    }
}