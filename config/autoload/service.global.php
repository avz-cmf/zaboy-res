<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 19.12.16
 * Time: 11:48 AM
 */

use zaboy\rest\DataStore\Eav\EavAbstractFactory;
use zaboy\rest\TableGateway\Factory\TableManagerMysqlFactory;
use zaboy\rest\DataStore\Aspect\Factory\AspectAbstractFactory;
use zaboy\rest\Middleware\Factory\DataStoreAbstractFactory as MiddlewareDataStoreAbstractFactory;
use zaboy\rest\DataStore\Factory\HttpClientAbstractFactory;
use zaboy\rest\DataStore\Factory\DbTableAbstractFactory;
use zaboy\rest\DataStore\Factory\CsvAbstractFactory;
use zaboy\rest\DataStore\Factory\MemoryAbstractFactory;
use zaboy\rest\DataStore\Factory\CacheableAbstractFactory;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;
use zaboy\rest\TableGateway\Factory\TableGatewayAbstractFactory;

return [

    'services' => [
        'factories' => [
            'TableManagerMysql' => TableManagerMysqlFactory::class
        ],
        'abstract_factories' => [
            CsvAbstractFactory::class,
            MiddlewareDataStoreAbstractFactory::class,
            HttpClientAbstractFactory::class,
            DbTableAbstractFactory::class,
            MemoryAbstractFactory::class,
            AdapterAbstractServiceFactory::class,
        ],
        'aliases' => [
            EavAbstractFactory::DB_SERVICE_NAME => getenv('APP_ENV') === 'prod' ? 'db' : 'db',
            'logDataStore' => 'httpLogDS'
        ]
    ],

    'dataStore' => [
        'testCsvIntIdDS' => [
            'class' => zaboy\rest\DataStore\CsvBase::class ,
            'filename' => __DIR__ . DIRECTORY_SEPARATOR . ".." .
                DIRECTORY_SEPARATOR . "..".DIRECTORY_SEPARATOR . "data" .
                DIRECTORY_SEPARATOR . "csv-storage" . DIRECTORY_SEPARATOR . 'logs.csv',
            /*'filename' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testCsvIntId.tmp',*/
            'delimiter' => ';',
        ],
        'httpLogDS' => [
            'class' => zaboy\rest\DataStore\HttpClient::class,
            'url' => 'http://localhost:8080/api/rest/testCsvIntIdDS',
            'options' => ['timeout' => 30]
        ]
    ]
];
