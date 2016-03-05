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
class SqlConditionBuilder extends ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => '(' , 'between' => ' AND ' , 'after' =>')'],
        ],
        'ArrayOperator' => [
            
        ],
        'ScalarOperator' => [
            'eq' => ['before' => '(' , 'between' => '=' , 'after' =>')'],
            'ne' => ['before' => '(' , 'between' => '<>' , 'after' =>')'],        
            'ge' => ['before' => '(' , 'between' => '=>' , 'after' =>')'],
            'gt' => ['before' => '(' , 'between' => '>' , 'after' =>')'],   
            'le' => ['before' => '(' , 'between' => '<=' , 'after' =>')'],
            'lt' => ['before' => '(' , 'between' => '<' , 'after' =>')'], 
        ]
    ];
    
    protected $supportedQueryNodeNames = [];

    protected $emptyCondition;
    
    /**
     *
     * @var AdapterInterface 
     */
    protected $db; 
    
    /**
     * 
     * @param array $options
     */
    public function __construct(AdapterInterface $dbAdapter)
    {
        parent::__construct();
        $this->db = $dbAdapter;           
        $this->emptyCondition = 
                $this->prepareFildValue(1) 
                . ' = '
                . $this->prepareFildValue(1); 
 
    }
    
    
    public function prepareFildName($fildName) 
    {
        return $this->db->platform->quoteIdentifier($fildName);
    }
    
    public function prepareFildValue($fildValue) 
    {
        return $this->db->platform->quoteValue($fildValue);
    }
}