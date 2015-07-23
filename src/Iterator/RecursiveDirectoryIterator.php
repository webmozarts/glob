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

if ((version_compare(PHP_VERSION, '5.5.23', '>=') && version_compare(PHP_VERSION, '5.6', '<'))
    || version_compare(PHP_VERSION, '5.6.7', '>=')) {

    /**
     * Redefines the native {@link \RecursiveDirectoryIterator} under a
     * different name.
     *
     * {@link class_alias()} doesn't work for native classes.
     *
     * @since  1.0
     *
     * @author Bernhard Schussek <bschussek@gmail.com>
     */
    class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
    {
    }

} else {

    /**
     * Recursive directory iterator that is working during recursive iteration.
     *
     * This implementation is very slow compared to PHP's native implementation.
     *
     * @since  1.0
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

}
