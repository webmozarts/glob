<?php

/*
 * This file is part of the webmozart/glob package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Glob\Iterator;

use Iterator;
use RecursiveIterator;
use RuntimeException;

/**
 * Recursively iterates over a list of of paths.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursivePathsIterator implements RecursiveIterator
{
    /**
     * Flag: Return current value as file path.
     */
    const CURRENT_AS_PATH = 1;

    /**
     * Flag: Return current value as file name.
     */
    const CURRENT_AS_FILE = 2;

    /**
     * @var Iterator
     */
    private $innerIterator;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var bool
     */
    private $failed;

    public function __construct(Iterator $innerIterator, $flags = null)
    {
        if (!($flags & (self::CURRENT_AS_FILE | self::CURRENT_AS_PATH))) {
            $flags |= self::CURRENT_AS_PATH;
        }

        $this->innerIterator = $innerIterator;
        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (!$this->valid()) {
            return null;
        }

        return ($this->flags & self::CURRENT_AS_FILE)
            ? basename($this->innerIterator->current())
            : $this->innerIterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (!$this->valid()) {
            return null;
        }

        return $this->innerIterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (!$this->valid()) {
            return;
        }

        $this->innerIterator->next();

        $this->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return !$this->failed && $this->innerIterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->innerIterator->rewind();

        $this->failed = false;

        $this->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        if (!$this->valid()) {
            return false;
        }

        return is_dir($this->innerIterator->current());
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new RecursiveDirectoryIterator($this->current());
    }

    private function validate()
    {
        if ($this->innerIterator->valid()) {
            if (!file_exists($path = $this->innerIterator->current())) {
                $this->failed = true;

                throw new RuntimeException(sprintf(
                    'The path "%s" was expected to be a file.',
                    $path
                ));
            }
        }
    }
}
