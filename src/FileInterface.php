<?php
declare(strict_types=1);

namespace Mjml;

/**
 * Interface FileInterface
 * @package Mjml
 */
interface FileInterface
{
    /**
     * Tells if the file is executable
     *
     * @return bool true if executable, false otherwise.
     */
    public function isExecutable();

    /**
     * Returns the path to the file as a string
     *
     * @return string
     */
    public function __toString();
}