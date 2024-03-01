<?php

declare(strict_types=1);

namespace webdeveric\WordPressInstallFixer\Tasks;

class RemoveIndexFile extends Task
{
    /**
     * Remove the fixed index.php file
     */
    public function run() : bool
    {
        return $this->filesystem->unlink($this->directory . '/index.php');
    }
}
