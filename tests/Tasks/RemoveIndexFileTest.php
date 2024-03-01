<?php

namespace webdeveric\WordPressInstallFixer\Tests\Tasks;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use webdeveric\WordPressInstallFixer\Tasks\RemoveIndexFile;

class RemoveIndexFileTest extends TestCase
{
    public function testRun()
    {
        $root = vfsStream::setup('public', null, [
            'index.php' => '',
        ]);

        $this->assertTrue($root->hasChild('index.php'));

        $task = new RemoveIndexFile($root->url('public'));
        $this->assertTrue($task->run());

        $this->assertFalse($root->hasChild('index.php'));
    }
}
