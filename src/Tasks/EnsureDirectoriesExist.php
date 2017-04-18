<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer\Tasks;

class EnsureDirectoriesExist extends Task
{
    /**
     * Ensure some directories exist
     *
     * @return bool
     */
    public function run() : bool
    {
        $path = rtrim($this->directory, DIRECTORY_SEPARATOR);

        $directories = [
            'wp-content/themes',
            'wp-content/plugins',
            'wp-content/mu-plugins',
        ];

        foreach ($directories as $dir) {
            $this->filesystem->ensureDirectoryExists($path . DIRECTORY_SEPARATOR . $dir);
        }

        return true;
    }
}
