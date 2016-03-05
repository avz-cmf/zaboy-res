<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\ConditionBuilder;

use zaboy\res\DataStores\ConditionBuilderAbstract;
use Zend\Db\Adapter\AdapterInterface;

/**
 * 
 */
class RqlConditionBuilder extends ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => 'and(' , 'between' => ',' , 'after' =>')'],
        ],
        'ArrayOperator' => [
            
        ],
        'ScalarOperator' => [
            'eq' => ['before' => 'eq(' , 'between' => ',' , 'after' =>')'],
            'ne' => ['before' => 'ne(' , 'between' => ',' , 'after' =>')'],        
            'ge' => ['before' => 'ge(' , 'between' => ',' , 'after' =>')'],
            'gt' => ['before' => 'gt(' , 'between' => ',' , 'after' =>')'],   
            'le' => ['before' => 'le(' , 'between' => ',' , 'after' =>')'],
            'lt' => ['before' => 'lt(' , 'between' => ',' , 'after' =>')'], 
        ]
    ];

    protected $emptyCondition= '';

}