<?php

declare(strict_types=1);

namespace webdeveric\WordPressInstallFixer;

use RuntimeException;
use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event as ScriptEvent;
use Composer\Util\Filesystem;
use webdeveric\WordPressInstallFixer\Tasks\EnsureDirectoriesExist;
use webdeveric\WordPressInstallFixer\Tasks\FixIndexFile;
use webdeveric\WordPressInstallFixer\Tasks\RemoveGarbage;
use webdeveric\WordPressInstallFixer\Tasks\RemoveIndexFile;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    const string PACKAGE_TYPE = 'wordpress-core';

    protected Composer $composer;

    protected IOInterface $io;

    /**
     * Where WordPress is installed
     */
    protected string $wpInstallDir;

    /**
     * Subscribe to events
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL  => ['handlePackageInstallOrUpdate', 0],
            PackageEvents::POST_PACKAGE_UPDATE   => ['handlePackageInstallOrUpdate', 0],
            PackageEvents::PRE_PACKAGE_UNINSTALL => ['handlePackageUninstall', 0],
        ];
    }

    public function handlePackageInstallOrUpdate(PackageEvent | ScriptEvent $event): void
    {
        if ($this->isWordPressCore($event)) {
            $filesystem = new Filesystem();

            (new FixIndexFile($this->wpInstallDir, $filesystem))->run();

            $this->comment('index.php fixed');

            (new RemoveGarbage($this->wpInstallDir, $filesystem))->run();

            $this->comment('Unneeded files have been removed from the WordPress install directory');

            (new EnsureDirectoriesExist($this->wpInstallDir . '/../', $filesystem))->run();

            $this->comment('WordPress content directories exist');
        }
    }

    public function handlePackageUninstall(/* PackageEvent | ScriptEvent $event */): void
    {
        (new RemoveIndexFile($this->wpInstallDir . '/../', new Filesystem()))->run();

        $this->comment('index.php removed');
    }

    /**
     * Activate the plugin
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->wpInstallDir = $this->getExtra('wordpress-install-dir');

        if (!is_string($this->wpInstallDir) || !strlen($this->wpInstallDir)) {
            throw new RuntimeException('wordpress-install-dir not set in composer.json');
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // Do nothing
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // Do nothing
    }

    /**
     * Get data from composer.json/extra
     */
    protected function getExtra(string $key, $default = ''): mixed
    {
        $extra = $this->composer->getPackage()->getExtra();

        return isset($extra[$key]) ? $extra[$key] : $default;
    }

    /**
     * Make a comment, if verbose (cli option -v)
     */
    protected function comment(string $message): void
    {
        if ($this->io->isVerbose()) {
            $this->io->write("<comment>{$message}</comment>");
        }
    }

    /**
     * Determine if the package event is for the WordPress core
     */
    protected function isWordPressCore(PackageEvent | ScriptEvent $event): bool
    {
        if ($event instanceof PackageEvent) {
            $operation = $event->getOperation();

            if ($operation instanceof UpdateOperation) {
                return $operation->getTargetPackage()->getType() === self::PACKAGE_TYPE;
            }

            if ($operation instanceof UninstallOperation || $operation instanceof InstallOperation) {
                return $operation->getPackage()->getType() === self::PACKAGE_TYPE;
            }
        }

        return false;
    }
}
