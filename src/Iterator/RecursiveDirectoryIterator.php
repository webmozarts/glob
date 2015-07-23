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

/**
 * Recursive directory iterator that is working during recursive iteration.
 *
 * Recursive iteration is broken on PHP < 5.5.23 and on PHP 5.6 < 5.6.7.
 *
 * @since  1.0
 * @since  3.0 Removed support for seek(), added \RecursiveDirectoryIterator
 *             base class, adapted API to match \RecursiveDirectoryIterator
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new static($this->getPathname(), $this->getFlags());
    }
}
