<?php

namespace LPLabs\WordPressInstallFixer\Tests\Tasks;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use LPLabs\WordPressInstallFixer\Tasks\RemoveGarbage;

class RemoveGarbageTest extends TestCase
{
    public function testRun()
    {
        $garbage = [
            'wp-content' => [],
            'readme.html' => '',
            'license.txt' => '',
        ];

        $root = vfsStream::setup('public', null, [
            'cms' => $garbage,
        ]);

        $cms = $root->getChild('cms');

        foreach (array_keys($garbage) as $path) {
            $this->assertTrue($cms->hasChild($path));
        }

        $task = new RemoveGarbage($cms->url());
        $this->assertTrue($task->run());

        foreach (array_keys($garbage) as $path) {
            $this->assertFalse($cms->hasChild($path));
        }
    }
}
