<?php

return [ 
    'dataStore' => [
        'testDbTable' => [
            'class' =>'zaboy\res\DataStore\DbTable',
            'tableName' => 'test_res_tablle'
            ],
        'testMemory' => [
            'class' =>'zaboy\res\DataStore\Memory',
            ]
    ],
    'middleware' => [
        'MiddlewareMemoryTest' => [
          'class' =>'zaboy\res\Middleware\MiddlewareMemoryStore',
          'dataStore' => 'testMemory'
        ]
    ],   
    'services' => [
        'factories' => [
            'db' => 'zaboy\res\Db\Adapter\AdapterFactory'       
        ],
        'abstract_factories' => [
            'zaboy\res\DataStores\Factory\DbTableStoresAbstractFactory',
            'zaboy\res\DataStores\Factory\MemoryStoresAbstractFactory' ,
            'zaboy\res\Middlewares\Factory\MiddlewareStoreAbstractFactory'
        ]    
    ]
];