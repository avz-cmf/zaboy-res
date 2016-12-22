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
            EavAbstractFactory::class,
            AspectAbstractFactory::class,
            MiddlewareDataStoreAbstractFactory::class,
            HttpClientAbstractFactory::class,
            DbTableAbstractFactory::class,
            CsvAbstractFactory::class,
            MemoryAbstractFactory::class,
            CacheableAbstractFactory::class,
            AdapterAbstractServiceFactory::class,
            TableGatewayAbstractFactory::class,
        ],
        'aliases' => [
            EavAbstractFactory::DB_SERVICE_NAME => getenv('APP_ENV') === 'prod' ? 'db' : 'db',
            'logDataStore' => 'testMemDS'
        ]
    ],

    'dataStore' => [
        'testMemDS' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ]
    ]
];
