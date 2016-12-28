<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 23.12.16
 * Time: 12:18 PM
 */

namespace zaboy\res\Di\Example\ExtendedIC;


use zaboy\res\Di\InsideConstruct;

class SimpleDependency
{
    protected $simpleStringA;
    public $simpleNumericB;
    private $simpleArrayC;

    public function __construct($simpleStringA = 'simpleStringA',
                                $simpleNumericB = 2.4,
                                $simpleArrayC = [0 => 'simpleArrayC'])
    {
        InsideConstruct::initMyServices();
    }

    /**
     * @return mixed
     */
    public function getSimpleStringA()
    {
        return $this->simpleStringA;
    }

    /**
     * @return mixed
     */
    public function getSimpleArrayC()
    {
        return $this->simpleArrayC;
    }
}