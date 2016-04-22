<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\test\res\DataStore\ConditionBuilder;

use Xiag\Rql\Parser\DataType\Glob;

/**
 *
 */
abstract class ConditionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /*
     * var PhpConditionBuilder
     */

    protected $object;

    abstract public function providerPrepareFildName();

    /**
     * @dataProvider providerPrepareFildName
     */
    public function testPrepareFildName($in, $out)
    {
        $fildName = $this->object->prepareFildName($in);
        $this->assertEquals(
                $out, $fildName
        );
    }

    abstract public function providerGetValueFromGlob();

    /**
     * @dataProvider providerGetValueFromGlob
     */
    public function testGetValueFromGlob($in, $out)
    {
        $globOgject = new Glob($in);
        $value = $this->object->getValueFromGlob($globOgject);
        $this->assertEquals(
                $out, $value
        );
    }

    abstract public function provider__invoke();

    /**
     * @dataProvider provider__invoke
     */
    public function test__invoke($rootQueryNode, $out)
    {
        $condition = $this->object->__invoke($rootQueryNode);
        $this->assertEquals(
                $out, $condition
        );
    }

}
