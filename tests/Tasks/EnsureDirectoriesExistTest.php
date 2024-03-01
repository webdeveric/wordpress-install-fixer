<?php

namespace webdeveric\WordPressInstallFixer\Tests\Tasks;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\TestCase;
use webdeveric\WordPressInstallFixer\Tasks\EnsureDirectoriesExist;

class EnsureDirectoriesExistTest extends TestCase
{
    public function testRun()
    {
        $root = vfsStream::setup('public');

        $task = new EnsureDirectoriesExist($root->url());

        $this->assertEquals(
            [
                'public' => [],
            ],
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );

        $this->assertTrue($task->run());

        $this->assertEquals(
            [
                'public' => [
                    'wp-content' => [
                        'mu-plugins' => [],
                        'plugins' => [],
                        'themes' => [],
                    ],
                ],
            ],
            vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure()
        );
    }
}
