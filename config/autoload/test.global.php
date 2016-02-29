<?php

return [ 
    'dataStore' => [
        'testDbTable' => [
            'class' =>'zaboy\res\DataStore\DbTable',
            'tableName' => 'test_res_tablle'
            ],
        'testHttpClient' => [
            'class' =>'zaboy\res\DataStore\HttpClient',
            'url' => 'rest/test_res_tablle',
            'options' => ['timeout' => 30]
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