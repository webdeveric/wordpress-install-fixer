<?php

namespace LPLabs\WordPressInstallFixer\Tests;

use Composer\Config;
use Composer\Composer;
use Composer\IO\ConsoleIO;
use PHPUnit\Framework\TestCase;
use LPLabs\WordPressInstallFixer\Plugin;

class PluginTest extends TestCase
{
    protected $composer;
    protected $io;

    protected function setUp()
    {
        $this->composer = new Composer();
        $this->io = $this->createMock('Composer\IO\ConsoleIO');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testExtraMissing()
    {
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

        $this->assertInternalType('array', Plugin::getSubscribedEvents());
    }
}
