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

use FilterIterator;
use Iterator;

/**
 * Filters an iterator by a regular expression.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    Glob
 */
class RegexFilterIterator extends FilterIterator
{
    /**
     * Mode: Filters the values of the inner iterator.
     */
    const FILTER_VALUE = 1;

    /**
     * Mode: Filters the keys of the inner iterator.
     */
    const FILTER_KEY = 2;

    /**
     * @var string
     */
    private $regExp;

    /**
     * @var string
     */
    private $staticPrefix;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @var int
     */
    private $mode;

    /**
     * Creates a new iterator.
     *
     * @param string   $regExp        The regular expression to filter by.
     * @param string   $staticPrefix  The static prefix of the regular
     *                                expression.
     * @param Iterator $innerIterator The filtered iterator.
     * @param int      $mode          A bitwise combination of the mode constants.
     */
    public function __construct($regExp, $staticPrefix, Iterator $innerIterator, $mode = self::FILTER_VALUE)
    {
        parent::__construct($innerIterator);

        $this->regExp = $regExp;
        $this->staticPrefix = $staticPrefix;
        $this->mode = $mode;
    }

    /**
     * Rewind the iterator to the first position.
     */
    public function rewind()
    {
        parent::rewind();

        $this->cursor = 0;
    }

    /**
     * Returns the current position.
     *
     * @return int The current position.
     */
    public function key()
    {
        return $this->cursor;
    }

    /**
     * Advances to the next match.
     *
     * @see Iterator::next()
     */
    public function next()
    {
        parent::next();

        ++$this->cursor;
    }

    /**
     * Accepts paths matching the glob.
     *
     * @return bool Whether the path is accepted.
     */
    public function accept()
    {
        $path = ($this->mode & self::FILTER_VALUE) ? $this->current() : parent::key();

        if (0 !== strpos($path, $this->staticPrefix)) {
            return false;
        }

        return (bool) preg_match($this->regExp, $path);
    }
}
