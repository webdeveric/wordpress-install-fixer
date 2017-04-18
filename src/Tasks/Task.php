<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer\Tasks;

use RuntimeException;
use Composer\Util\Filesystem;

abstract class Task
{
    protected $directory;
    protected $filesystem;

    /**
     * @param string $directory
     * @throws RuntimeException
     */
    public function __construct(string $directory, Filesystem $filesystem = null)
    {
        $this->directory  = $directory;
        $this->filesystem = $filesystem ?? new Filesystem;

        if (! file_exists($this->directory)) {
            throw new RuntimeException("{$directory} does not exist");
        }
    }

    /**
     * Perform the task
     *
     * @return bool
     */
    abstract public function run() : bool;
}
