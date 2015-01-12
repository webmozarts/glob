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

use Webmozart\Glob\Iterator\GlobIterator;

/**
 * Utility methods for handling globs.
 *
 * This class implements a Git-lik version of PHP's `glob()` function. The
 * wildcard "*" matches any number of characters, *including* directory
 * separators.
 *
 * Use {@link glob()} to glob the filesystem for paths:
 *
 * ```php
 * foreach (Glob::glob('/project/*.twig') as $path) {
 *     // do something...
 * }
 * ```
 *
 * Use {@link match()} to match a file path against a glob:
 *
 * ```php
 * if (Glob::match('/project/views/index.html.twig', '/project/*.twig')) {
 *     // path matches
 * }
 * ```
 *
 * You can also filter an array of paths for all paths that match your glob with
 * {@link filter()}:
 *
 * ```php
 * $filteredPaths = Glob::filter($paths, '/project/*.twig');
 * ```
 *
 * Internally, the methods described above convert the glob into a regular
 * expression that is then matched against the matched paths. If you need to
 * match many paths against the same glob, you should convert the glob manually
 * and use {@link preg_match()} to test the paths:
 *
 * ```php
 * $staticPrefix = Glob::getStaticPrefix('/project/*.twig');
 * $regEx = Glob::toRegEx('/project/*.twig');
 *
 * if (0 !== strpos($path, $staticPrefix)) {
 *     // no match
 * }
 *
 * if (!preg_match($regEx, $path)) {
 *     // no match
 * }
 * ```
 *
 * The method {@link getStaticPrefix()} returns the part of the glob up to the
 * first wildcard "*". You should always test whether a path has this prefix
 * before calling the much more expensive {@link preg_match()}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Glob
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
     * Matches a literal "\" when running a regular expression against another
     * regular expression.
     */
    const E_BACKSLASH = '\\\\\\\\';

    /**
     * Matches a literal "*" when running a regular expression against another
     * regular expression.
     */
    const E_STAR = '\\\\\\*';

    /**
     * Globs the file system paths matching the glob.
     *
     * The glob may contain the wildcard "*". This wildcard matches any number
     * of characters, *including* directory separators.
     *
     * ```php
     * foreach (Glob::glob('/project/*.twig') as $path) {
     *     // do something...
     * }
     * ```
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "webmozart/path-util" utility to canonicalize globs
     *                     prior to calling this method.
     *
     * @return string[] The matching paths. The keys of the array are
     *                  incrementing integers.
     */
    public static function glob($glob)
    {
        $results = iterator_to_array(new GlobIterator($glob));

        sort($results);

        return $results;
    }

    /**
     * Matches a path against a glob.
     *
     * ```php
     * if (Glob::match('/project/views/index.html.twig', '/project/*.twig')) {
     *     // path matches
     * }
     * ```
     *
     * @param string $path The path to match.
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "webmozart/path-util" utility to canonicalize globs
     *                     prior to calling this method.
     *
     * @return bool Returns `true` if the path is matched by the glob.
     */
    public static function match($path, $glob)
    {
        if (false === strpos($glob, '*')) {
            return $glob === $path;
        }

        if (0 !== strpos($path, self::getStaticPrefix($glob))) {
            return false;
        }

        if (!preg_match(self::toRegEx($glob), $path)) {
            return false;
        }

        return true;
    }

    /**
     * Filters an array for paths matching a glob.
     *
     * The filtered array is returned. This array preserves the keys of the
     * passed array.
     *
     * ```php
     * $filteredPaths = Glob::filter($paths, '/project/*.twig');
     * ```
     *
     * @param string[] $paths A list of paths.
     * @param string   $glob  The canonical glob. The glob should contain
     *                        forward slashes as directory separators only. It
     *                        must not contain any "." or ".." segments. Use the
     *                        "webmozart/path-util" utility to canonicalize
     *                        globs prior to calling this method.
     *
     * @return string[] The paths matching the glob indexed by their original
     *                  keys.
     */
    public static function filter(array $paths, $glob)
    {
        if (false === strpos($glob, '*')) {
            return in_array($glob, $paths) ? array($glob) : array();
        }

        $staticPrefix = self::getStaticPrefix($glob);
        $regExp = self::toRegEx($glob);

        return array_filter($paths, function ($path) use ($staticPrefix, $regExp) {
            return 0 === strpos($path, $staticPrefix) && preg_match($regExp, $path);
        });
    }

    /**
     * Returns the base path of a glob.
     *
     * This method returns the most specific directory that contains all files
     * matched by the glob. If this directory does not exist on the file system,
     * it's not necessary to execute the glob algorithm.
     *
     * More specifically, the "base path" is the longest path trailed by a "/"
     * on the left of the first wildcard "*". If the glob does not contain
     * wildcards, the directory name of the glob is returned.
     *
     * ```php
     * Glob::getBasePath('/css/*.css');
     * // => /css
     *
     * Glob::getBasePath('/css/style.css');
     * // => /css
     *
     * Glob::getBasePath('/css/st*.css');
     * // => /css
     *
     * Glob::getBasePath('/*.css');
     * // => /
     * ```
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "webmozart/path-util" utility to canonicalize globs
     *                     prior to calling this method.
     *
     * @return string The base path of the glob.
     */
    public static function getBasePath($glob)
    {
        // Start searching for a "/" at the last character
        $offset = -1;

        // If the glob contains a wildcard "*", start searching for the
        // "/" on the left of the wildcard
        if (false !== ($pos = strpos($glob, '*'))) {
            $offset = $pos - strlen($glob);
        }

        if (false !== ($pos = strrpos($glob, '/', $offset))) {
            // Special case: Return "/" if the only slash is at the beginning
            // of the glob
            if (0 === $pos) {
                return '/';
            }

            return substr($glob, 0, $pos);
        }

        // Glob contains no slashes on the left of the wildcard
        // Return an empty string
        return '';
    }

    /**
     * Converts a glob to a regular expression.
     *
     * Use this method if you need to match many paths against a glob:
     *
     * ```php
     * $staticPrefix = Glob::getStaticPrefix('/project/*.twig');
     * $regEx = Glob::toRegEx('/project/*.twig');
     *
     * if (0 !== strpos($path, $staticPrefix)) {
     *     // no match
     * }
     *
     * if (!preg_match($regEx, $path)) {
     *     // no match
     * }
     * ```
     *
     * You should always test whether a path contains the static prefix of the
     * glob returned by {@link getStaticPrefix()} to reduce the number of calls
     * to the expensive {@link preg_match()}.
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "webmozart/path-util" utility to canonicalize globs
     *                     prior to calling this method.
     *
     * @return string The regular expression for matching the glob.
     */
    public static function toRegEx($glob)
    {
        // From the PHP manual: To specify a literal single quote, escape it
        // with a backslash (\). To specify a literal backslash, double it (\\).
        // All other instances of backslash will be treated as a literal backslash.

        // This method does the following replacements:

        // Normal wildcards:    "*"  => ".*" (regex match any)
        // Escaped wildcards:   "\*" => "\*" (regex star)
        // Escaped backslashes: "\\" => "\\" (regex backslash)

        // Other characters are escaped as usual for regular expressions.

        // Quote regex characters
        $quoted = preg_quote($glob, '~');

        // Replace "*" by ".*", as long as preceded by an even number of backslashes
        $regEx = preg_replace(
            '~(?<!'.self::E_BACKSLASH.')(('.self::E_BACKSLASH.self::E_BACKSLASH.')*)'.self::E_STAR.'~',
            '$1.*',
            $quoted
        );

        // Replace "\*" by "*"
        $regEx = str_replace(self::BACKSLASH.self::STAR, self::STAR, $regEx);

        // Replace "\\\\" by "\\"
        // (escaped backslashes were escaped again by preg_quote())
        $regEx = str_replace(self::E_BACKSLASH, self::BACKSLASH, $regEx);

        return '~^'.$regEx.'$~';
    }

    /**
     * Returns the static prefix of a glob.
     *
     * The "static prefix" is the part of the glob up to the first wildcard "*".
     * If the glob does not contain wildcards, the full glob is returned.
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "webmozart/path-util" utility to canonicalize globs
     *                     prior to calling this method.
     *
     * @return string The static prefix of the glob.
     */
    public static function getStaticPrefix($glob)
    {
        if (false !== ($pos = strpos($glob, '*'))) {
            return substr($glob, 0, $pos);
        }

        return $glob;
    }

    private function __construct()
    {
    }
}
