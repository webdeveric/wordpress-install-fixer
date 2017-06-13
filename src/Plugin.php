<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer;

use RuntimeException;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\Filesystem;
use LPLabs\WordPressInstallFixer\Tasks\EnsureDirectoriesExist;
use LPLabs\WordPressInstallFixer\Tasks\FixIndexFile;
use LPLabs\WordPressInstallFixer\Tasks\RemoveGarbage;
use LPLabs\WordPressInstallFixer\Tasks\RemoveIndexFile;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var string
     */
    const PACKAGE_TYPE = 'wordpress-core';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * Where WordPress is installed
     *
     * @var string
     */
    protected $wpInstallDir;

    /**
     * Subscribe to events
     *
     * @return array
     */
    public static function getSubscribedEvents() : array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL  => [ 'handleEvent', 0 ],
            PackageEvents::POST_PACKAGE_UPDATE   => [ 'handleEvent', 0 ],
            PackageEvents::PRE_PACKAGE_UNINSTALL => [ 'handleEvent', 0 ],
        ];
    }

    /**
     * Activate the plugin
     * @param  Composer    $composer
     * @param  IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->wpInstallDir = $this->getExtra('wordpress-install-dir', false);

        if (! $this->wpInstallDir) {
            throw new RuntimeException('wordpress-install-dir not set in composer.json');
        }
    }

    /**
     * Get data from composer.json/extra
     *
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    protected function getExtra(string $key, $default = '')
    {
        $extra = $this->composer->getPackage()->getExtra();

        return isset($extra[ $key ]) ? $extra[ $key ] : $default;
    }

    /**
     * Make a comment, if verbose (cli option -v)
     *
     * @param  string $message
     */
    protected function comment(string $message)
    {
        if ($this->io->isVerbose()) {
            $this->io->write("<comment>{$message}</comment>");
        }
    }

    /**
     * Handle an event
     *
     * @param  PackageEvent $event
     */
    public function handleEvent(PackageEvent $event)
    {
        if ($this->isWordPressCore($event)) {
            $filesystem = new Filesystem;
            $eventName = $event->getName();

            if ($eventName === PackageEvents::POST_PACKAGE_INSTALL || $eventName === PackageEvents::POST_PACKAGE_UPDATE) {
                (new FixIndexFile($this->wpInstallDir, $filesystem))->run();
                $this->comment('index.php fixed');

                (new RemoveGarbage($this->wpInstallDir, $filesystem))->run();
                $this->comment('Unneeded files have been removed from the WordPress install directory');

                (new EnsureDirectoriesExist($this->wpInstallDir . '/../', $filesystem))->run();
                $this->comment('WordPress content directories exist');
            } elseif ($eventName === PackageEvents::PRE_PACKAGE_UNINSTALL) {
                (new RemoveIndexFile($this->wpInstallDir . '/../', $filesystem))->run();
                $this->comment('index.php removed');
            }
        }
    }

    /**
     * Determine if the package event is for the WordPress core
     *
     * @param  PackageEvent $event
     * @return boolean
     */
    protected function isWordPressCore(PackageEvent $event) : bool
    {
        $operation = $event->getOperation();
        $package = method_exists($operation, 'getTargetPackage') ? $operation->getTargetPackage() : $operation->getPackage();

        return $package->getType() === self::PACKAGE_TYPE;
    }
}
