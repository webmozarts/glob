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

use InvalidArgumentException;
use RecursiveIterator;
use SeekableIterator;

/**
 * Recursive directory iterator with a working seek() method and working
 * behavior during recursive iteration.
 *
 * See https://bugs.php.net/bug.php?id=68557
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveDirectoryIterator implements RecursiveIterator, SeekableIterator
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
     * @var resource
     */
    private $handle;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $current;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var int
     */
    private $position;

    /**
     * Creates an iterator for the given path.
     *
     * @param string $path  A canonical directory path.
     * @param int    $flags The flags.
     */
    public function __construct($path, $flags = null)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'The path "%s" was expected to be a directory.',
                $path
            ));
        }

        if (!($flags & (self::CURRENT_AS_FILE | self::CURRENT_AS_PATH))) {
            $flags |= self::CURRENT_AS_PATH;
        }

        $this->path = '/' === substr($path, -1) ? $path : $path.'/';
        $this->flags = $flags;
    }

    public function __destruct()
    {
        if (null !== $this->handle) {
            closedir($this->handle);
            $this->handle = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $file = readdir($this->handle);

        if (false === $file) {
            closedir($this->handle);
            $this->current = null;
            $this->key = null;
            $this->handle = null;
            $this->position = -1;

            return;
        }

        if ('.' === $file || '..' === $file) {
            $this->next();

            return;
        }

        $path = $this->path.$file;

        // handle concurrent deletions
        if (!file_exists($path)) {
            $this->next();

            return;
        }

        $this->key = $path;
        $this->current = ($this->flags & self::CURRENT_AS_FILE) ? $file : $this->key;
        ++$this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->handle = opendir($this->path);
        $this->position = -1;

        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren()
    {
        return is_dir($this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new static($this->key, $this->flags);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        if ($this->position > $position || null === $this->handle) {
            $this->rewind();
        }

        while ($this->position < $position) {
            $this->next();
        }
    }
}
