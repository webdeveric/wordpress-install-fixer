<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer\Tasks;

use RuntimeException;

class RemoveIndexFile extends Task
{
    /**
     * Remove the fixed index.php file
     *
     * @return bool
     */
    public function run() : bool
    {
        return $this->filesystem->unlink($this->directory . '/index.php');
    }
}
