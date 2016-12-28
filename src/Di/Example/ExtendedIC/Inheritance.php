<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Di\Example\ExtendedIC;

use zaboy\res\Di\InsideConstruct;

class Inheritance extends PropertiesDefault
{

//    //From parent
//    public $propA;
//    protected $propB;
//    private $propC;

    public function __construct($newPropA = null)
    {
        $result = InsideConstruct::initMyServices();
        InsideConstruct::initParentService(['useDi' => true, 'propA' => $result['newPropA']]);
    }

}
