<?php

namespace LPLabs\WordPressInstallFixer\Tests\Tasks;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use LPLabs\WordPressInstallFixer\Tasks\EnsureDirectoriesExist;

class EnsureDirectoriesExistTest extends TestCase
{
    public function testRun()
    {
        $root = vfsStream::setup('public');
        $task = new EnsureDirectoriesExist($root->url());

        $this->assertFalse($root->hasChild('wp-content'));
        $this->assertTrue($task->run());
        $this->assertTrue($root->hasChild('wp-content'));

        foreach ([ 'themes', 'plugins', 'mu-plugins' ] as $folder) {
            $this->assertTrue($root->getChild('wp-content')->hasChild($folder));
        }
    }
}
