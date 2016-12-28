<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\res\Di;

use Interop\Container\ContainerInterface;

class InsideConstruct
{

    /**
     * Use next in head af scripts
     * <code>
     * require 'vendor/autoload.php';
     * $container = include 'config/container.php';
     * //add:
     * InsideConstruct::setContainer( $container )
     * <code>
     *
     * @var ContainerInterface
     */
    protected static $container = null;

    protected static function checkContainer()
    {
        global $container;
        static::$container = static::$container ? static::$container : $container;
        if (!(isset(static::$container) && static::$container instanceof ContainerInterface)) {
            throw new \UnexpectedValueException(
                'global $contaner or InsideConstruct::$contaner'
                . ' must be inited'
            );
        }
    }

    protected static function checkConstruct(\ReflectionMethod $refConstruct)
    {
        //!проверяет наличие метода construct, а не нахождения в нем
        if (!isset($refConstruct)) {
            throw new \LengthException(
                'You must call InsideConstruct::initServices() inside Construct only'
            );
        }
    }

    public static function initMyServices()
    {
        $result = [];

        InsideConstruct::checkContainer();

        //Who call me?;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $className = $trace[1]['class'];
        $reflectionClass = new \ReflectionClass($className);
        /* @var $reflectionClass \ReflectionClass */
        $object = $trace[1]['object'];
        $args = $trace[1]['args'];

        //I need your __construct params
        //$reflectionClass->getMethod('__construct');
        InsideConstruct::checkConstruct(
            ($refConstruct = $reflectionClass->getConstructor()));

        $refParams = $refConstruct->getParameters();
        // $refParams array of ReflectionParameter
        foreach ($refParams as $refParam) {
            /* @var $refParam \ReflectionParameter */
            $paramName = $refParam->getName();

            //Is param retrived?
            if (empty($args)) {
                //Do this param need in service loading
                //Has service in $container?
                $paramValue = self::getParamValue($paramName, $refParam);
            } else {
                //Value for param was retrived in __construct().
                $paramValue = array_shift($args);
            }
            $result[$paramName] = $paramValue;
            InsideConstruct::setValue($reflectionClass, $paramName, $paramValue, $object);
        }
        return $result;
    }

    protected static function setValue(\ReflectionClass $reflectionClass, $paramName, $paramValue, $object)
    {
        //setters
        $methodName = 'set' . ucfirst($paramName);
        $refMethod = $reflectionClass->hasMethod($methodName) ?
            $reflectionClass->getMethod($methodName) :
            null;
        //properties
        $refProperty = $reflectionClass->hasProperty($paramName) ?
            $reflectionClass->getProperty($paramName) :
            null;

        if (isset($refMethod) && $refMethod->isPublic()) {
            $refMethod->invoke($object, $paramValue);
        } else if (isset($refMethod) && ($refMethod->isPrivate() || $refMethod->isProtected())) {
            $refMethod->setAccessible(true);
            $refMethod->invoke($object, $paramValue);
            $refMethod->setAccessible(false);
        } else if (isset($refProperty) && $refProperty->isPublic()) {
            $refProperty->setValue($object, $paramValue);
        } else if (isset($refProperty) && ($refProperty->isPrivate() || $refProperty->isProtected())) {
            $refProperty->setAccessible(true);
            $refProperty->setValue($object, $paramValue);
            $refProperty->setAccessible(false);
        }
    }

    public static function initParentService(array $loadParams = [])
    {
        InsideConstruct::checkContainer();

        //Who call me?;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $className = $trace[1]['class'];
        $reflectionClass = new \ReflectionClass($className);
        $object = $trace[1]['object'];
        /* @var $reflectionClass \ReflectionClass */

        $refParentClass = $reflectionClass->getParentClass();
        InsideConstruct::checkConstruct(
            ($refParentConstruct = $refParentClass->getConstructor()));

        $refParams = $refParentConstruct->getParameters();

        return self::parentService($loadParams, $refParams, $object, $refParentConstruct);
    }


    public static function init(array $mapping = [])
    {
        $result = [];
        $loadParams = [];

        InsideConstruct::checkContainer();

        //Who call me?;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $className = $trace[1]['class'];
        $reflectionClass = new \ReflectionClass($className);
        /* @var $reflectionClass \ReflectionClass */
        $object = $trace[1]['object'];
        $args = $trace[1]['args'];

        //I need your __construct params
        //$reflectionClass->getMethod('__construct');
        InsideConstruct::checkConstruct(
            ($refConstruct = $reflectionClass->getConstructor()));

        $refParams = $refConstruct->getParameters();
        // $refParams array of ReflectionParameter

        $refParentClass = $reflectionClass->getParentClass();
        InsideConstruct::checkConstruct(
            ($refParentConstruct = $refParentClass->getConstructor()));

        $refParentParams = $refParentConstruct->getParameters();
        foreach ($refParams as $refParam) {
            $paramName = $refParam->getName();

            if (empty($args)) {
                //Do this param need in service loading
                //Has service in $container?
                $paramValue = self::getParamValue($paramName, $refParam);
            } else {
                //Value for param was retrived in __construct().
                $paramValue = array_shift($args);
            }
            if (!in_array($refParam, $refParentParams) && !isset($mapping[$paramName])) {
                $result[$paramName] = $paramValue;
                InsideConstruct::setValue($reflectionClass, $paramName, $paramValue, $object);
            } else {
                if (isset($mapping[$paramName])){
                    $loadParams[$mapping[$paramName]] = $paramValue;
                }else {
                    $loadParams[$paramName] = $paramValue;
                }
            }
        }
        $parentResult = self::parentService($loadParams, $refParams, $object, $refParentConstruct);
        return array_merge($result, $parentResult);
    }

    public static function setContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * @param $paramName
     * @param $refParam
     * @return mixed
     */
    protected static function getParamValue($paramName, \ReflectionParameter $refParam)
    {
        if (static::$container->has($paramName)) {
            $paramValue = static::$container->get($paramName); // >getType()
            $paramClass = $refParam->getClass() ? $refParam->getClass()->getName() : null;
            if ($paramClass && !($paramValue instanceof $paramClass)) {
                throw new \LogicException(
                    'Wrong type for service: ' . $paramName
                );
            }
        } else {
            $paramValue = $refParam->getDefaultValue();
        }
        return $paramValue;
    }

    /**
     * @param array $loadParams
     * @param $refParams
     * @param $object
     * @param \ReflectionMethod $refParentConstruct
     * @return mixed
     */
    protected static function parentService(array $loadParams, $refParams, $object, \ReflectionMethod $refParentConstruct)
    {
        $params = '';
        $result = [];

        foreach ($refParams as $refParam) {
            $paramName = $refParam->getName();
            if (!isset($loadParams[$paramName])) {
                $paramValue = self::getParamValue($paramName, $refParam);
            } else {
                $paramValue = $loadParams[$paramName];
            }
            $result[$paramName] = $paramValue;
            $params .= '$result["' . $paramName . '"],';
        }
        //gen call method signature with params
        $params = trim($params, ',');
        $callRefParConstruct = '$refParentConstruct->invoke($object,' . $params . ');';
        eval($callRefParConstruct);
        return $result;
    }

    /**
     * @param array $loadServices
     * @return array
     * @deprecated
     */
    public static function initServices($loadServices = [])
    {
        $result = [];
        global $container;
        static::$container = static::$container ? static::$container : $container;
        if (!(isset(static::$container) && static::$container instanceof ContainerInterface)) {
            throw new \UnexpectedValueException(
                'global $contaner or InsideConstruct::$contaner'
                . ' must be inited'
            );
        }
        //Who call me?;
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $className = $trace[1]['class'];
        $reflectionClass = new \ReflectionClass($className);
        /* @var $reflectionClass \ReflectionClass */
        $object = $trace[1]['object'];
        $args = $trace[1]['args'];
        //I need your __construct params
        $refConstruct = $reflectionClass->getConstructor(); //$reflectionClass->getMethod('__construct');
        if (!isset($refConstruct)) {
            throw new \LengthException(
                'You must call InsideConstruct::initServices() inside Construct only'
            );
        }
        $refParams = $refConstruct->getParameters();
        // $refParams array of ReflectionParameter
        foreach ($refParams as $refParam) {
            /* @var $refParam \ReflectionParameter */
            $paramName = $refParam->getName();
            //setters
            $methodName = 'set' . ucfirst($paramName);
            $refMethod = $reflectionClass->hasMethod($methodName) ?
                $reflectionClass->getMethod($methodName) :
                null;
            //properties
            $refProperty = $reflectionClass->hasProperty($paramName) ?
                $reflectionClass->getProperty($paramName) :
                null;
            //Is param retrived?
            if (empty($args)) {
                //Do this param need in service loading
                if ($refMethod || $refProperty || in_array($paramName, $loadServices)) {
                    //Has service in $container?
                    if (!static::$container->has($paramName)) {
                        throw new \LogicException(
                            'Can not load service - "' . $paramName . '" for param - $' . $paramName
                        );
                    }
                    $paramValue = static::$container->get($paramName); // >getType()
                    $paramClass = $refParam->getClass() ? $refParam->getClass()->getName() : null;
                    if ($paramClass && !($paramValue instanceof $paramClass)) {
                        throw new \LogicException(
                            'Wrong type for service: ' . $paramName
                        );
                    }
                } else {
                    $paramValue = $refParam->getDefaultValue();
                }
            } else {
                //Value for param was retrived in __construct().
                $paramValue = array_shift($args);
            }
            $result[$paramName] = $paramValue;
            if (isset($refMethod) && $refMethod->isPublic()) {
                $refMethod->invoke($object, $paramValue);
                continue;
            }
            if (isset($refMethod) && ($refMethod->isPrivate() || $refMethod->isProtected())) {
                $refMethod->setAccessible(true);
                $refMethod->invoke($object, $paramValue);
                $refMethod->setAccessible(false);
                continue;
            }
            if (isset($refProperty) && $refProperty->isPublic()) {
                $refProperty->setValue($object, $paramValue);
                continue;
            }
            if (isset($refProperty) && ( $refProperty->isPrivate() || $refProperty->isProtected())) {
                $refProperty->setAccessible(true);
                $refProperty->setValue($object, $paramValue);
                $refProperty->setAccessible(false);
                continue;
            }
        }
        return $result;
    }
}
