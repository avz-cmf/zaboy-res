<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 23.12.16
 * Time: 3:16 PM
 */

namespace zaboy\test\res\Di;

use zaboy\res\Di\Example\ExtendedIC\InheritanceSimpleDependency;
use zaboy\res\Di\Example\ExtendedIC\SettersDefault;
use zaboy\res\Di\Example\ExtendedIC\SimpleDependency;
use zaboy\res\Di\Example\ExtendedIC\Inheritance;
use zaboy\res\Di\InsideConstruct;


class ExtendedInsideConstructTest extends InsideConstructTest
{

    public function testInitServices_SimpleDependency()
    {
        $this->container->method('has')
            ->will($this->returnValue(false));
        $tested = new SimpleDependency();

        $this->assertEquals('simpleStringA', $tested->getSimpleStringA());
        $this->assertEquals(2.4, $tested->simpleNumericB);
        $this->assertEquals([0 => 'simpleArrayC'], $tested->getSimpleArrayC());
    }

    public function testInitServices_InheritanceSimpleDependency()
    {
        $this->container->method('has')
            ->will($this->returnValue(false));
        $tested = new InheritanceSimpleDependency();

        $this->assertEquals('simpleString_A', $tested->getSimpleStringA());
        $this->assertEquals(2.4, $tested->simpleNumericB);
        $this->assertEquals([0 => 'simpleArrayC'], $tested->getSimpleArrayC());
    }

    public function testInitServices_SettersDefault()
    {
        $mapHas = [
            ['propA', true],
            ['propB', true],
            ['propC', true],
        ];
        $this->container->method('has')
            ->will($this->returnValueMap($mapHas));

        $mapGet = [
            ['propA', 'PropA value'],
            ['propB', new \ArrayObject()],
            ['propC', new \stdClass()],
        ];

        $this->container->method('get')
            ->will($this->returnValueMap($mapGet));


        $useDiTrue = true;
        $tested = new SettersDefault($useDiTrue);
        $diResult = $useDiTrue; //by reference
        $useDiFalse = false;
        $expected = new SettersDefault($useDiFalse, 'PropA value', new \ArrayObject(), new \stdClass());
        unset($diResult['useDi']);
        $this->assertEquals(
            [ 'propA' => 'PropA value', 'propB' => new \ArrayObject(), 'propC' => new \stdClass()], $diResult
        );
        $this->assertEquals($expected, $tested);


        $useDiTrue = true;
        $tested = new SettersDefault($useDiTrue, null, 'PropB value');
        $diResult = $useDiTrue; //by reference
        $useDiFalse = false;
        $expected = new SettersDefault($useDiFalse, null, 'PropB value', new \stdClass());
        unset($diResult['useDi']);
        $this->assertEquals(
            [ 'propA' => null, 'propB' => 'PropB value', 'propC' => new \stdClass()], $diResult
        );
        $this->assertEquals($expected, $tested);
    }

    public function testInitServices_Inheritance()
    {
        $mapHas = [
            ['propA', true],
            ['propB', true],
            ['propC', true],
            ['newPropA', true],
        ];
        $this->container->method('has')
            ->will($this->returnValueMap($mapHas));

        $mapGet = [
            ['propA', 'PropA value'],
            ['propB', new \ArrayObject()],
            ['propC', new \stdClass()],
            ['newPropA', 'PropNewA value'],
        ];

        $this->container->method('get')
            ->will($this->returnValueMap($mapGet));

        $tested = new Inheritance();

        $this->assertEquals(
            'PropNewA value', $tested->propA
        );
    }
}
