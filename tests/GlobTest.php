<?php

/*
 * This file is part of the webmozart/glob package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Glob\Tests;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Glob;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobTest extends PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        while (false === @mkdir($this->tempDir = sys_get_temp_dir().'/webmozart-glob/GlobTest'.rand(10000, 99999), 0777, true)) {}

        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__.'/Fixtures', $this->tempDir);

        TestStreamWrapper::register('globtest', __DIR__.'/Fixtures');
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->tempDir);

        TestStreamWrapper::unregister('globtest');
    }

    public function testGlob()
    {
        $this->assertSame(array(
            $this->tempDir.'/base.css',
        ), Glob::glob($this->tempDir.'/*.css'));

        $this->assertSame(array(
            $this->tempDir.'/base.css',
            $this->tempDir.'/css',
        ), Glob::glob($this->tempDir.'/*css*'));

        $this->assertSame(array(
            $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css',
        ), Glob::glob($this->tempDir.'/*/*.css'));

        $this->assertSame(array(
            $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css',
        ), Glob::glob($this->tempDir.'/*/**/*.css'));

        $this->assertSame(array(
            $this->tempDir.'/base.css',
            $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css',
        ), Glob::glob($this->tempDir.'/**/*.css'));

        $this->assertSame(array(
            $this->tempDir.'/base.css',
            $this->tempDir.'/css',
            $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css',
        ), Glob::glob($this->tempDir.'/**/*css'));

        $this->assertSame(array(
            $this->tempDir.'/base.css',
            $this->tempDir.'/css/reset.css',
        ), Glob::glob($this->tempDir.'/**/{base,reset}.css'));

        $this->assertSame(array(
            $this->tempDir.'/css',
            $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css',
        ), Glob::glob($this->tempDir.'/css{,/**/*}'));

        $this->assertSame(array(), Glob::glob($this->tempDir.'/*foo*'));
    }

    public function testGlobStreamWrapper()
    {
        $this->assertSame(array(
            'globtest:///base.css',
        ), Glob::glob('globtest:///*.css'));

        $this->assertSame(array(
            'globtest:///base.css',
            'globtest:///css',
        ), Glob::glob('globtest:///*css*'));

        $this->assertSame(array(
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ), Glob::glob('globtest:///*/*.css'));

        $this->assertSame(array(
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ), Glob::glob('globtest:///*/**/*.css'));

        $this->assertSame(array(
            'globtest:///base.css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ), Glob::glob('globtest:///**/*.css'));

        $this->assertSame(array(
            'globtest:///base.css',
            'globtest:///css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ), Glob::glob('globtest:///**/*css'));

        $this->assertSame(array(
            'globtest:///base.css',
            'globtest:///css/reset.css',
        ), Glob::glob('globtest:///**/{base,reset}.css'));

        $this->assertSame(array(
            'globtest:///css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ), Glob::glob('globtest:///css{,/**/*}'));

        $this->assertSame(array(), Glob::glob('globtest:///*foo*'));
    }

    public function testGlobEscape()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('A "*" in filenames is not supported on Windows.');

            return;
        }

        touch($this->tempDir.'/css/style*.css');
        touch($this->tempDir.'/css/style{.css');
        touch($this->tempDir.'/css/style}.css');

        $this->assertSame(array(
            $this->tempDir.'/css/style*.css',
            $this->tempDir.'/css/style.css',
            $this->tempDir.'/css/style{.css',
            $this->tempDir.'/css/style}.css',
        ), Glob::glob($this->tempDir.'/css/style*.css'));

        $this->assertSame(array(
            $this->tempDir.'/css/style*.css',
        ), Glob::glob($this->tempDir.'/css/style\\*.css', Glob::ESCAPE));

        $this->assertSame(array(), Glob::glob($this->tempDir.'/css/style\\*.css'));

        $this->assertSame(array(
            $this->tempDir.'/css/style{.css',
        ), Glob::glob($this->tempDir.'/css/style\\{.css', Glob::ESCAPE));

        $this->assertSame(array(), Glob::glob($this->tempDir.'/css/style\\{.css'));

        $this->assertSame(array(
            $this->tempDir.'/css/style}.css',
        ), Glob::glob($this->tempDir.'/css/style\\}.css', Glob::ESCAPE));

        $this->assertSame(array(), Glob::glob($this->tempDir.'/css/style\\}.css'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testGlobFailsIfNotAbsolute()
    {
        Glob::glob('*.css');
    }

    /**
     * @dataProvider provideWildcardMatches
     */
    public function testToRegEx($path, $isMatch)
    {
        $regExp = Glob::toRegEx('/foo/*.js~');

        $this->assertSame($isMatch, preg_match($regExp, $path));
    }

    /**
     * @dataProvider provideDoubleWildcardMatches
     */
    public function testToRegExDoubleWildcard($path, $isMatch)
    {
        $regExp = Glob::toRegEx('/foo/**/*.js~');

        $this->assertSame($isMatch, preg_match($regExp, $path));
    }

    public function provideWildcardMatches()
    {
        return array(
            // The method assumes that the path is already consolidated
            array('/bar/baz.js~', 0),
            array('/foo/baz.js~', 1),
            array('/foo/../bar/baz.js~', 0),
            array('/foo/../foo/baz.js~', 0),
            array('/bar/baz.js', 0),
            array('/foo/bar/baz.js~', 0),
            array('foo/baz.js~', 0),
            array('/bar/foo/baz.js~', 0),
            array('/bar/.js~', 0),
        );
    }

    public function provideDoubleWildcardMatches()
    {
        return array(
            array('/bar/baz.js~', 0),
            array('/foo/baz.js~', 1),
            array('/foo/../bar/baz.js~', 1),
            array('/foo/../foo/baz.js~', 1),
            array('/bar/baz.js', 0),
            array('/foo/bar/baz.js~', 1),
            array('foo/baz.js~', 0),
            array('/bar/foo/baz.js~', 0),
            array('/bar/.js~', 0),
        );
    }

    // From the PHP manual: To specify a literal single quote, escape it with a
    // backslash (\). To specify a literal backslash, double it (\\).
    // All other instances of backslash will be treated as a literal backslash

    public function testEscapedWildcard()
    {
        // evaluates to "\*"
        $regExp = Glob::toRegEx('/foo/\\*.js~', Glob::ESCAPE);

        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\*.js~'));
    }

    public function testEscapedWildcard2()
    {
        // evaluates to "\*"
        $regExp = Glob::toRegEx('/foo/\*.js~', Glob::ESCAPE);

        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\*.js~'));
    }

    public function testEscapedWildcardIgnoredIfNoEscapeFlag()
    {
        // evaluates to "\*"
        $regExp = Glob::toRegEx('/foo/\\*.js~');

        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/*.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\\*.js~'));
    }

    public function testMatchEscapedWildcard()
    {
        // evaluates to "\*"
        $regExp = Glob::toRegEx('/foo/\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/*.js~'));
    }

    public function testMatchEscapedDoubleWildcard()
    {
        // evaluates to "\*\*"
        $regExp = Glob::toRegEx('/foo/\\*\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/**.js~'));
    }

    public function testMatchWildcardWithLeadingBackslash()
    {
        // evaluates to "\\*"
        $regExp = Glob::toRegEx('/foo/\\\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
    }

    public function testMatchWildcardWithLeadingBackslash2()
    {
        // evaluates to "\\*"
        $regExp = Glob::toRegEx('/foo/\\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
    }

    public function testMatchEscapedWildcardWithLeadingBackslash()
    {
        // evaluates to "\\\*"
        $regExp = Glob::toRegEx('/foo/\\\\\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\*.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\baz.js~'));
    }

    public function testMatchWildcardWithTwoLeadingBackslashes()
    {
        // evaluates to "\\\\*"
        $regExp = Glob::toRegEx('/foo/\\\\\\\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\\\baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
    }

    public function testMatchEscapedWildcardWithTwoLeadingBackslashes()
    {
        // evaluates to "\\\\*"
        $regExp = Glob::toRegEx('/foo/\\\\\\\\\\*.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\\\*.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\\\*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/*.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\\\baz.js~'));
    }

    public function testMatchEscapedLeftBrace()
    {
        $regExp = Glob::toRegEx('/foo/\\{.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/{.js~'));
    }

    public function testMatchLeftBraceWithLeadingBackslash()
    {
        $regExp = Glob::toRegEx('/foo/\\\\{b,c}az.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
    }

    public function testMatchEscapedLeftBraceWithLeadingBackslash()
    {
        $regExp = Glob::toRegEx('/foo/\\\\\\{b,c}az.js~', Glob::ESCAPE);

        $this->assertSame(0, preg_match($regExp, '/foo/\\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/\baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\\{b,c}az.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/\{b,c}az.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/{b,c}az.js~'));
    }

    public function testMatchEscapedRightBrace()
    {
        $regExp = Glob::toRegEx('/foo/\\}.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/}.js~'));
    }

    public function testMatchRightBraceWithLeadingBackslash()
    {
        $regExp = Glob::toRegEx('/foo/{b,c\\\\}az.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/c\\az.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/c\az.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/caz.js~'));
    }

    public function testMatchEscapedRightBraceWithLeadingBackslash()
    {
        $regExp = Glob::toRegEx('/foo/{b,c\\\\\\}}az.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/c\\}az.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/c\}az.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/c\\az.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/c\az.js~'));
    }

    public function testCloseBracesAsSoonAsPossible()
    {
        $regExp = Glob::toRegEx('/foo/{b,c}}az.js~', Glob::ESCAPE);

        $this->assertSame(1, preg_match($regExp, '/foo/b}az.js~'));
        $this->assertSame(1, preg_match($regExp, '/foo/c}az.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/baz.js~'));
        $this->assertSame(0, preg_match($regExp, '/foo/caz.js~'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testToRegexFailsIfNotAbsolute()
    {
        Glob::toRegEx('*.css');
    }

    /**
     * @dataProvider provideStaticPrefixes
     */
    public function testGetStaticPrefix($glob, $prefix, $flags = 0)
    {
        $this->assertSame($prefix, Glob::getStaticPrefix($glob, $flags));
    }

    public function provideStaticPrefixes()
    {
        return array(
            // The method assumes that the path is already consolidated
            array('/foo/baz/../*/bar/*', '/foo/baz/../'),
            array('/foo/baz/bar*', '/foo/baz/bar'),
            array('/foo/baz/bar\\*', '/foo/baz/bar\\'),
            array('/foo/baz/bar\\\\*', '/foo/baz/bar\\\\'),
            array('/foo/baz/bar\\*', '/foo/baz/bar*', Glob::ESCAPE),
            array('/foo/baz/bar\\\\*', '/foo/baz/bar\\', Glob::ESCAPE),
            array('/foo/baz/bar\\\\\\*', '/foo/baz/bar\\*', Glob::ESCAPE),
            array('/foo/baz/bar\\\\\\\\*', '/foo/baz/bar\\\\', Glob::ESCAPE),
            array('/foo/baz/bar\\*\\\\', '/foo/baz/bar*\\', Glob::ESCAPE),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testGetStaticPrefixFailsIfNotAbsolute()
    {
        Glob::getStaticPrefix('*.css');
    }

    /**
     * @dataProvider provideBasePaths
     */
    public function testGetBasePath($glob, $basePath, $flags = 0)
    {
        $this->assertSame($basePath, Glob::getBasePath($glob, $flags));
    }

    /**
     * @dataProvider provideBasePaths
     */
    public function testGetBasePathStream($glob, $basePath, $flags = 0)
    {
        $this->assertSame('globtest://'.$basePath, Glob::getBasePath('globtest://'.$glob, $flags));
    }

    public function provideBasePaths()
    {
        return array(
            // The method assumes that the path is already consolidated
            array('/foo/baz/../*/bar/*', '/foo/baz/..'),
            array('/foo/baz/bar*', '/foo/baz'),
            array('/foo/baz/bar', '/foo/baz'),
            array('/foo/baz*', '/foo'),
            array('/foo*', '/'),
            array('/*', '/'),
            array('/foo/baz*/bar', '/foo'),
            array('/foo/baz\\*/bar', '/foo'),
            array('/foo/baz\\\\*/bar', '/foo'),
            array('/foo/baz\\\\\\*/bar', '/foo'),
            array('/foo/baz*/bar', '/foo', Glob::ESCAPE),
            array('/foo/baz\\*/bar', '/foo/baz*', Glob::ESCAPE),
            array('/foo/baz\\\\*/bar', '/foo', Glob::ESCAPE),
            array('/foo/baz\\\\\\*/bar', '/foo/baz\\*', Glob::ESCAPE),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testGetBasePathFailsIfNotAbsolute()
    {
        Glob::getBasePath('*.css');
    }

    /**
     * @dataProvider provideDoubleWildcardMatches
     */
    public function testMatch($path, $isMatch)
    {
        $this->assertSame((bool) $isMatch, Glob::match($path, '/foo/**/*.js~'));
    }

    public function testMatchPathWithoutWildcard()
    {
        $this->assertTrue(Glob::match('/foo/bar.js~', '/foo/bar.js~'));
        $this->assertFalse(Glob::match('/foo/bar.js', '/foo/bar.js~'));
    }

    public function testMatchEscaped()
    {
        $this->assertTrue(Glob::match('/foo/bar*.js~', '/foo/bar*.js~', Glob::ESCAPE));
        $this->assertTrue(Glob::match('/foo/bar\\*.js~', '/foo/bar*.js~', Glob::ESCAPE));
        $this->assertTrue(Glob::match('/foo/bar\\baz.js~', '/foo/bar*.js~', Glob::ESCAPE));
        $this->assertTrue(Glob::match('/foo/bar*.js~', '/foo/bar\\*.js~', Glob::ESCAPE));
        $this->assertFalse(Glob::match('/foo/bar\\*.js~', '/foo/bar\\*.js~', Glob::ESCAPE));
        $this->assertFalse(Glob::match('/foo/bar\\baz.js~', '/foo/bar\\*.js~', Glob::ESCAPE));
        $this->assertFalse(Glob::match('/foo/bar*.js~', '/foo/bar\\\\*.js~', Glob::ESCAPE));
        $this->assertTrue(Glob::match('/foo/bar\\*.js~', '/foo/bar\\\\*.js~', Glob::ESCAPE));
        $this->assertTrue(Glob::match('/foo/bar\\baz.js~', '/foo/bar\\\\*.js~', Glob::ESCAPE));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testMatchFailsIfNotAbsolute()
    {
        Glob::match('/foo/bar.css', '*.css');
    }

    public function testFilter()
    {
        $paths = array();
        $filtered = array();

        // The keys remain the same in the filtered array
        $i = 0;

        foreach ($this->provideDoubleWildcardMatches() as $input) {
            $paths[$i] = $input[0];

            if ($input[1]) {
                $filtered[$i] = $input[0];
            }

            ++$i;
        }

        $this->assertSame($filtered, Glob::filter($paths, '/foo/**/*.js~'));
    }

    public function testFilterWithoutWildcard()
    {
        $paths = array(
            '/foo',
            '/foo/bar.js',
        );

        $this->assertSame(array(1 => '/foo/bar.js'), Glob::filter($paths, '/foo/bar.js'));
        $this->assertSame(array(), Glob::filter($paths, '/foo/bar.js~'));
    }

    public function testFilterEscaped()
    {
        $paths = array(
            '/foo',
            '/foo*.js',
            '/foo/bar.js',
            '/foo/bar*.js',
            '/foo/bar\\*.js',
            '/foo/bar\\baz.js',
        );

        $this->assertSame(array(
            1 => '/foo*.js',
            3 => '/foo/bar*.js',
            4 => '/foo/bar\\*.js',
        ), Glob::filter($paths, '/**/*\\*.js', Glob::ESCAPE));
    }

    public function testFilterNonEscaped()
    {
        $paths = array(
            '/foo',
            '/foo*.js',
            '/foo/bar.js',
            '/foo/bar*.js',
            '/foo/bar\\*.js',
            '/foo/bar\\baz.js',
        );

        $this->assertSame(array(
            4 => '/foo/bar\\*.js',
            5 => '/foo/bar\\baz.js',
        ), Glob::filter($paths, '/**/*\\*.js'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage *.css
     */
    public function testFilterFailsIfNotAbsolute()
    {
        Glob::filter(array('/foo/bar.css'), '*.css');
    }
}
