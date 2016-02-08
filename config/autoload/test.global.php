<?php

return [ 
    'dataStore' => [
        'testDbTable' => [
            'class' =>'zaboy\res\DataStore\DbTable',
            'tableName' => 'test_zav_res'
            ],
        'testMemory' => [
            'class' =>'zaboy\res\DataStore\Memory',
            ]
    ],
    'middleware' => [
        'MiddlewareMemoryTest' => [
          'class' =>'zaboy\res\Middleware\StoreMiddlewareMemory',
          'dataStore' => 'testMemory'
        ]
    ],   
    'services' => [
        'factories' => [
            'db' => 'zaboy\res\Db\Adapter\AdapterFactory'       
        ],
        'abstract_factories' => [
            'zaboy\res\DataStores\AbstractFactory\DbTableStoresAbstractFactory',
            'zaboy\res\DataStores\AbstractFactory\MemoryStoresAbstractFactory' ,
            'zaboy\res\Middlewares\Factory\StoreMiddlewareAbstractFactory'
        ]    
    ]
];