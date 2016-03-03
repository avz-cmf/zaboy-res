<?php

return [ 
    'dataStore' => [
        'testDbTable' => [
            'class' =>'zaboy\res\DataStore\DbTable',
            'tableName' => 'test_res_tablle'
            ],
        'testHttpClient' => [
            'class' =>'zaboy\res\DataStore\HttpClient',
            'tableName' => 'test_res_http',
            'url' => 'http://__zaboy-rest/api/rest/test_res_http',
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
        ],
        'abstract_factories' => [
            'zaboy\res\DataStores\Factory\DbTableStoresAbstractFactory',
            'zaboy\res\DataStores\Factory\MemoryStoresAbstractFactory' ,
            'zaboy\res\DataStores\Factory\HttpClientStoresAbstractFactory',            
            'zaboy\res\Middlewares\Factory\MiddlewareStoreAbstractFactory'

        ]    
    ]
];