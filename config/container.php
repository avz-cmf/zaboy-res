<?php
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
// Create a ServiceManager from service_manager config and register the merged config as a service
$config = include __DIR__ . '/config.php';
$configObject = new Config(isset($config['services']) ? $config['services'] : []);
$sm = new ServiceManager($configObject->toArray());
$sm->setService('config', $config);
// Return the fully configured ServiceManager
return $sm;