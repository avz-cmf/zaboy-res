<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\DataStore\ConditionBuilder;

use zaboy\res\DataStores\ConditionBuilderAbstract;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * 
 */
class RqlConditionBuilder extends ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => 'and(' , 'between' => ',' , 'after' =>')'],
            'or' => ['before' => 'or(' , 'between' => ',' , 'after' =>')'],
            'not' => ['before' => 'not' , 'between' => ',' , 'after' =>')'],
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
            
            'like' => ['before' => 'like(' , 'between' => ',' , 'after' =>')'], 
        ]
    ];

    protected $emptyCondition= '';
    
    public static function encodeString($value)
    {
        return strtr(rawurlencode($value), [
            '-' => '%2D',
            '_' => '%5F',
            '.' => '%2E',
            '~' => '%7E',
        ]);
    }
    
    public function prepareFildValue($fildValue) 
    {
        $constStar = 'starhjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        $constQuestion = 'questionhjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        
        $regexRqlDecoded = parent::prepareFildValue($fildValue);
        $regexRqlEnecoded =  self::encodeString($regexRqlDecoded);
        $regexRqlPrepared = strtr($regexRqlEnecoded, [$constStar => '*', $constQuestion => '?']);
        return $regexRqlPrepared;
    }
    
    
    public function getValueFromGlob(Glob $globNode) 
    {
        $constStar = 'starhjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        $constQuestion = 'questionhjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        
        $glob = parent::getValueFromGlob($globNode);
        
        $regexRqlPrepared = strtr($glob, ['*' => $constStar, '?' => $constQuestion]);
        $regexRqlDecoded = rawurldecode($regexRqlPrepared);
        return $regexRqlDecoded;
    }
}