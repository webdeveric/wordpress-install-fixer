<?php

namespace webdeveric\WordPressInstallFixer\Tests;

use Composer\Config;
use Composer\Composer;
use Composer\IO\ConsoleIO;
use PHPUnit\Framework\TestCase;
use webdeveric\WordPressInstallFixer\Plugin;

class PluginTest extends TestCase
{
    protected $composer;
    protected $io;

    protected function setUp() : void
    {
        $this->composer = new Composer();
        $this->io = $this->createMock('Composer\IO\ConsoleIO');
    }

    public function testExtraMissing()
    {
        $this->expectException(\RuntimeException::class);

        $this->composer->setPackage($this->createMock('Composer\Package\RootPackageInterface'));

        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);
    }

    public function testActivate()
    {
        $package = $this->createMock('Composer\Package\RootPackageInterface');

        $package->method('getExtra')->willReturn([
            'wordpress-install-dir' => 'cms',
        ]);

        $this->composer->setPackage($package);

        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);

        $this->assertIsArray(Plugin::getSubscribedEvents());
    }
}
