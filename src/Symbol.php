<?php

/*
 * This file is part of the webmozart/glob package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Glob;

/**
 * Contains symbol constants.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Symbol
{
    /**
     * Represents a literal "\" in a regular expression.
     */
    const BACKSLASH = '\\\\';

    /**
     * Represents a literal "*" in a regular expression.
     */
    const STAR = '\\*';

    /**
     * Represents a literal "{" in a regular expression.
     */
    const L_BRACE = '\\{';

    /**
     * Represents a literal "}" in a regular expression.
     */
    const R_BRACE = '\\}';

    /**
     * Matches a literal "\" when running a regular expression against
     * another regular expression.
     */
    const E_BACKSLASH = '\\\\\\\\';

    /**
     * Matches a literal "*" when running a regular expression against
     * another regular expression.
     */
    const E_STAR = '\\\\\\*';

    /**
     * Matches a literal "{" when running a regular expression against
     * another regular expression.
     */
    const E_L_BRACE = '\\\\\\{';

    /**
     * Matches a literal "}" when running a regular expression against
     * another regular expression.
     */
    const E_R_BRACE = '\\\\\\}';

    private function __construct()
    {
    }
}
