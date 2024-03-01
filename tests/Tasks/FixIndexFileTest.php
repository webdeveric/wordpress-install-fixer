<?php

namespace webdeveric\WordPressInstallFixer\Tests\Tasks;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use webdeveric\WordPressInstallFixer\Tasks\FixIndexFile;

class FixIndexFileTest extends TestCase
{
    public function testRun()
    {
        $root = vfsStream::setup('public', null, [
            'cms' => [
                'index.php' => '/wp-blog-header.php',
            ]
        ]);

        $task = new FixIndexFile($root->getChild('cms')->url('public'));

        $this->assertTrue($root->getChild('cms')->hasChild('index.php'));
        $this->assertFalse($root->hasChild('index.php'));
        $this->assertTrue($task->run());
        $this->assertTrue($root->hasChild('index.php'));
        $this->assertEquals(
            file_get_contents($root->getChild('index.php')->url()),
            '/cms/wp-blog-header.php'
        );
    }
}
