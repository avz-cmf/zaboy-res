<?php

namespace zaboy\test\DataStores\AbstractFactory;

use zaboy\middleware\Middlewares\Factory\StoreMiddlewareAbstractFactory;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class MemoryStoresAbstractFactoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Returner
     */
    protected $object;
    
    /*
     * @var Zend\Diactoros\Response
     */
    protected $response;
    
    /*
     * @var Zend\Diactoros\ServerRequest;
     */
    protected $request;
    
    /*
     * @var \Callable
     */
    protected $next;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    public function testStoreMiddlewareMemory__invoke() {
        $container = include 'config/container.php';
        $this->object = $container->get('testMemory');
        $this->assertSame(
                get_class($returnedResponse = $this->object),
                'zaboy\res\DataStore\Memory'
        );
    }
    
    

}
