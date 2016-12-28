<?php
/**
 *
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 27.12.16
 * Time: 4:02 PM
 */

namespace zaboy\install;

use Composer\Script\Event;
use Interop\Container\ContainerInterface;
use zaboy\Install\Installer;

abstract class AbstractCommand
{

    const INSTALL = 'install';

    const UNINSTALL = 'uninstall';

    const REINSTALL = 'reinstall';

    /**
     * avz-cmf [lib-name] => [
     *      "class" => 'InstallerCommands::Class'
     *      "installed" => true|false
     * ]
     * @var array
     **/
    protected static $dep = [];

    /** @var ContainerInterface */
    private static $container = null;

    /**
     * @return ContainerInterface
     */
    private static function getContainer()
    {
        if (!isset(AbstractCommand::$container))
            AbstractCommand::$container = include 'config/container.php';

        return AbstractCommand::$container;
    }


    /**
     * Return status lib or app
     * @return string
     */
    public static function whoIAm()
    {
        return preg_match('/\/vendor\//', __DIR__) == 1 ? "lib" : "app";
    }


    /**
     * do command for include installers.
     * Composer Event - for get dependencies and IO
     * @param Event $event
     * Type of command doю
     * @param $commandType
     * @param array $installers
     */
    protected static function command(Event $event, $commandType, array $installers)
    {
        $composer = $event->getComposer();
        $dependencies = $composer->getPackage()->getRequires();
        foreach ($dependencies as $dependency) {
            $target = $dependency->getTarget();
            $match = [];
            //get avz-cmf dependencies
            if (preg_match('/^avz-cmf\/([\w\-\_]+)$/', $target, $match)) {
                if (!isset(AbstractCommand::$dep[$match[1]])) {
                    $class = $match[1] . '\\InstallCommands';
                    AbstractCommand::$dep[$match[1]] = [
                        "class" => $class
                    ];
                    AbstractCommand::$dep[$match[1]]['installed'] = class_exists($class) ? 0 : -1;
                }
                //call command recursive by dep
                if (AbstractCommand::$dep[$match[1]]['installed'] == 0) {
                    /** @var AbstractCommand $installer */
                    (AbstractCommand::$dep[$match[1]]['class'])::{$commandType}($event);
                }
            }
        }

        /** @var InstallerInterface $installer */
        foreach ($installers as $installerClass) {
            $installer = new $installerClass(self::getContainer());
            $installer->{$commandType}();
        }
    }

    /**
     * return array with Install class for lib;
     * @return InstallerInterface []
     */
    abstract public static function getInstallers();

    /**
     * @param Event $event
     * @return void
     */
    abstract public static function install(Event $event);

    /**
     * @param Event $event
     * @return void
     */
    abstract public static function uninstall(Event $event);

    /**
     * @param Event $event
     * @return void
     */
    abstract public static function reinstall(Event $event);
}