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
class PhpConditionBuilder extends ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => '(' , 'between' => ' && ' , 'after' =>')'],
        ],
        'ArrayOperator' => [
            
        ],
        'ScalarOperator' => [
            'eq' => ['before' => '(' , 'between' => '==' , 'after' =>')'],
            'ne' => ['before' => '(' , 'between' => '!=' , 'after' =>')'],        
            'ge' => ['before' => '(' , 'between' => '>=' , 'after' =>')'],
            'gt' => ['before' => '(' , 'between' => '>' , 'after' =>')'],   
            'le' => ['before' => '(' , 'between' => '<=' , 'after' =>')'],
            'lt' => ['before' => '(' , 'between' => '<' , 'after' =>')'], 
        ]
    ];
    
    protected $supportedQueryNodeNames = [];

    protected $emptyCondition;
    
    /**
     * 
     * @param array $options
     */
    public function __construct()
    {
        parent::__construct();
        $this->emptyCondition = ' true '; 
 
    }
    public function prepareFildName($fildName) 
    {
        return '$item[\'' . $fildName . '\']';
    }
    
    public function prepareFildValue($fildValue) 
    {
        switch (true) {
            case is_bool($fildValue):
                $fildValue = (bool) $fildValue ? TRUE :FALSE;
                return $fildValue;
            case is_numeric($fildValue):
                return $fildValue;          
            case is_string($fildValue):
                return "'" . $fildValue . "'";
            default:
                throw new DataStoresException(
                    'Type ' . gettype($fildValue) . ' is not supported'
                );    
        }
    }
}