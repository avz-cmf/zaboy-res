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
use Xiag\Rql\Parser\DataType\Glob;

/**
 * 
 */
class SqlConditionBuilder extends ConditionBuilderAbstract
{  
    protected $literals = [
        'LogicOperator' => [
            'and' => ['before' => '(' , 'between' => ' AND ' , 'after' =>')'],
            'or' => ['before' => '(' , 'between' => ' OR ' , 'after' =>')'], 
            'not' => ['before' => '( NOT (' , 'between' => ' error ' , 'after' =>') )'], 
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
            
            'like' => ['before' => '(' , 'between' => ' LIKE ' , 'after' =>')'], 
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
        $fildValue = parent::prepareFildValue($fildValue);
        return $this->db->platform->quoteValue($fildValue);
    }
    
    
    public function getValueFromGlob(Glob $globNode) 
    {
        $constStar = 'star_hjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        $constQuestion = 'question_hjc7vjHg6jd8mv8hcy75GFt0c67cnbv74FegxtEDJkcucG64frblmkb';
        
        $glob= parent::getValueFromGlob($globNode);

        $regexSQL = strtr(
            preg_quote(rawurldecode(strtr($glob, ['*' => $constStar, '?' => $constQuestion])), '/'),
            [$constStar => '%', $constQuestion => '_']
        );

        return $regexSQL;
    }
}