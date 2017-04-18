<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer\Tasks;

class RemoveGarbage extends Task
{
    /**
     * Remove some unneeded files from the WordPress install directory
     *
     * @return bool
     */
    public function run() : bool
    {
        $garbage = [
            $this->directory . '/wp-content',
            $this->directory . '/readme.html',
            $this->directory . '/license.txt',
        ];

        foreach ($garbage as $path) {
            if (! $this->filesystem->remove($path)) {
                return false;
            }
        }

        return true;
    }
}
