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

use ArrayIterator;
use EmptyIterator;
use RecursiveIteratorIterator;
use Webmozart\Glob\Glob;

/**
 * Returns filesystem paths matching a glob.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    Glob
 */
class GlobIterator extends GlobFilterIterator
{
    /**
     * Creates a new iterator.
     *
     * @param string $glob  The glob pattern.
     * @param int    $flags A bitwise combination of the flag constants in
     *                      {@link Glob}.
     */
    public function __construct($glob, $flags = 0)
    {
        $basePath = Glob::getBasePath($glob);

        if (!Glob::isDynamic($glob) && file_exists($glob)) {
            // If the glob is a file path, return that path
            $innerIterator = new ArrayIterator(array($glob));
        } elseif (is_dir($basePath)) {
            // Otherwise scan the glob's base directory for matches
            $innerIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath),
                RecursiveIteratorIterator::SELF_FIRST
            );
        } else {
            // If the glob's base directory does not exist, return nothing
            $innerIterator = new EmptyIterator();
        }

        parent::__construct($glob, $innerIterator, self::FILTER_VALUE, $flags);
    }
}
