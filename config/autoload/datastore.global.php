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
    ]   
];