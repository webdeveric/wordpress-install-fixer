<?php

declare(strict_types=1);

namespace LPLabs\WordPressInstallFixer\Tasks;

use RuntimeException;

class FixIndexFile extends Task
{
    /**
     * Fix index.php to have the correct path to wp-blog-header.php
     *
     * @return bool
     * @throws RuntimeException Unable to write index.php
     */
    public function run() : bool
    {
        $src  = $this->directory . '/index.php';
        $dest = dirname($this->directory) . '/index.php';

        if (is_file($src) && is_readable($src)) {
            $folder = basename($this->directory);

            $bytes = file_put_contents(
                $dest,
                str_replace(
                    '/wp-blog-header.php',
                    "/{$folder}/wp-blog-header.php",
                    file_get_contents($src)
                )
            );

            if ($bytes === false) {
                throw new RuntimeException('Unable to write ' . $dest);
            }

            return file_exists($dest);
        }

        return false;
    }
}
