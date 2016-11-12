<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\Di\Example\InsideConstruct;

use zaboy\Di\InsideConstruct;

class Inheritance extends PropertiesDefault
{

//    //From parent
//    public $propA;
//    protected $propB;
//    private $propC;

    public function __construct($newPropA = null)
    {
        $params = InsideConstruct::initServices(['newPropA']);
        $propA = $params['newPropA'];
        parent::__construct(true, $propA);
    }

}
