<?php

namespace zaboy\test\Di;

use zaboy\Di\Example\InsideConstruct\PropertiesDefault;
use zaboy\Di\Example\InsideConstruct\SettersDefault;
use zaboy\Di\Example\InsideConstruct\Inheritance;
use Interop\Container\ContainerInterface;
use zaboy\Di\InsideConstruct;
use Zend\ServiceManager\ServiceManager;

class InsideConstructTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed
     */
    protected function setUp()
    {
        $this->container = $this->getMock(ContainerInterface::class);
        InsideConstruct::setContainer($this->container);
    }

    //==========================================================================

    public function testInitServices_PropertiesDefault()
    {
        $mapHas = [
            ['propA', false],
            ['propB', true],
            ['propC', true],
        ];
        $this->container->method('has')
                ->will($this->returnValueMap($mapHas));

        $mapGet = [

            ['propB', new \ArrayObject()],
            ['propC', new \stdClass()],
        ];

        $this->container->method('get')
                ->will($this->returnValueMap($mapGet));

        $tested = new PropertiesDefault(true, null);
        $expected = new PropertiesDefault(false, null, new \ArrayObject(), new \stdClass());

        $this->assertEquals($expected, $tested);

        $this->setExpectedException(\LogicException::class, 'Can not load service - "propA" for param - $propA');
        $tested = new PropertiesDefault(true);
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
