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

    const CLEAR = 'clear';

    const RE = 're';

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

    private static function getContainer()
    {
        if (!isset(AbstractCommand::$container))
            AbstractCommand::$container = include 'config/container.php';

        return AbstractCommand::$container;
    }

    protected static function command(Event $event, $commandType, array $installers)
    {
        $composer = $event->getComposer();
        $dependencies = $composer->getPackage()->getRequires();
        foreach ($dependencies as $dependency) {
            $target = $dependency->getTarget();
            $match = [];
            if (preg_match('/^avz-cmf\/([\w\-\_]+)$/', $target, $match)) {
                if (!isset(AbstractCommand::$dep[$match[1]])) {
                    AbstractCommand::$dep[$match[1]] = [
                        "class" => $match[1] . '\\InstallCommands',
                        "installed" => 0
                    ];
                }
                if (!AbstractCommand::$dep[$match[1]]['installed']) {
                    /** @var AbstractCommand $installer */
                    $installer = new AbstractCommand::$dep[$match[1]];
                    $installer->{$commandType}($event);
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
    abstract public static function clear(Event $event);

    /**
     * @param Event $event
     * @return void
     */
    abstract public static function re(Event $event);
}