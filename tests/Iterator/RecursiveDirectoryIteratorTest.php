<?php

/*
 * This file is part of the webmozart/glob package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Glob\Tests\Iterator;

use PHPUnit_Framework_TestCase;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Iterator\RecursiveDirectoryIterator;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveDirectoryIteratorTest extends PHPUnit_Framework_TestCase
{
    private $tempDir;

    protected function setUp()
    {
        while (false === mkdir($this->tempDir = sys_get_temp_dir().'/webmozart-glob/RecursiveDirectoryIteratorTest'.rand(10000, 99999), 0777, true)) {
        }

        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__.'/../Fixtures', $this->tempDir);
    }

    protected function tearDown()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->tempDir);
    }

    public function testIterate()
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->tempDir,
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
        );

        $this->assertSameAfterSorting(array(
            $this->tempDir.'/.' => $this->tempDir.'/.',
            $this->tempDir.'/..' => $this->tempDir.'/..',
            $this->tempDir.'/base.css' => $this->tempDir.'/base.css',
            $this->tempDir.'/css' => $this->tempDir.'/css',
            $this->tempDir.'/js' => $this->tempDir.'/js',
        ), iterator_to_array($iterator));
    }

    public function testIterateSkipDots()
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->tempDir,
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME | RecursiveDirectoryIterator::SKIP_DOTS
        );

        $this->assertSameAfterSorting(array(
            $this->tempDir.'/base.css' => $this->tempDir.'/base.css',
            $this->tempDir.'/css' => $this->tempDir.'/css',
            $this->tempDir.'/js' => $this->tempDir.'/js',
        ), iterator_to_array($iterator));
    }

    public function testIterateTrailingSlash()
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->tempDir.'/',
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
        );

        $this->assertSameAfterSorting(array(
            $this->tempDir.'/.' => $this->tempDir.'/.',
            $this->tempDir.'/..' => $this->tempDir.'/..',
            $this->tempDir.'/base.css' => $this->tempDir.'/base.css',
            $this->tempDir.'/css' => $this->tempDir.'/css',
            $this->tempDir.'/js' => $this->tempDir.'/js',
        ), iterator_to_array($iterator));
    }

    public function testIterateRecursively()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->tempDir,
                RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $this->assertSameAfterSorting(array(
            $this->tempDir.'/.' => $this->tempDir.'/.',
            $this->tempDir.'/..' => $this->tempDir.'/..',
            $this->tempDir.'/base.css' => $this->tempDir.'/base.css',
            $this->tempDir.'/css' => $this->tempDir.'/css',
            $this->tempDir.'/css/.' => $this->tempDir.'/css/.',
            $this->tempDir.'/css/..' => $this->tempDir.'/css/..',
            $this->tempDir.'/css/reset.css' => $this->tempDir.'/css/reset.css',
            $this->tempDir.'/css/style.css' => $this->tempDir.'/css/style.css',
            $this->tempDir.'/js' => $this->tempDir.'/js',
            $this->tempDir.'/js/.' => $this->tempDir.'/js/.',
            $this->tempDir.'/js/..' => $this->tempDir.'/js/..',
            $this->tempDir.'/js/script.js' => $this->tempDir.'/js/script.js',
        ), iterator_to_array($iterator));
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFailIfNonExistingBaseDirectory()
    {
        new RecursiveDirectoryIterator($this->tempDir.'/foobar');
    }

    /**
     * Compares that an array is the same as another after sorting.
     *
     * This is necessary since RecursiveDirectoryIterator is not guaranteed to
     * return sorted results on all filesystems.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    private function assertSameAfterSorting($expected, $actual, $message = '')
    {
        if (is_array($expected)) {
            ksort($expected);
        }

        if (is_array($actual)) {
            ksort($actual);
        }

        $this->assertSame($expected, $actual, $message);
    }
}
