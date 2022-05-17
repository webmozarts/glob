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

use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Iterator\GlobIterator;
use Webmozart\Glob\Test\TestUtil;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobIteratorErrorTest extends \PHPUnit\Framework\TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = TestUtil::makeTempDir('webmozart-glob', __CLASS__);

        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__.'/../Fixtures', $this->tempDir);
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        chmod($this->tempDir.'/js', 0777);
        $filesystem->remove($this->tempDir);
    }

    public function testIterateError()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('chmod tests only on Linux.');

            return;
        }
        $this->assertTrue(function_exists('posix_getuid'));
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Current user is root, cannot test errors because root can read everything.');

            return;
        }
        $this->expectException(\UnexpectedValueException::class);
        chmod($this->tempDir.'/js', 0111);
        $iterator = new GlobIterator($this->tempDir.'/**/*.css');
        iterator_to_array($iterator);
    }

    public function testIterateWithoutError()
    {
        if (defined('PHP_WINDOWS_VERSION_MAJOR')) {
            $this->markTestSkipped('chmod tests only on Linux.');

            return;
        }
        $this->assertTrue(function_exists('posix_getuid'));
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Current user is root, cannot test errors because root can read everything.');

            return;
        }
        chmod($this->tempDir.'/js', 0111);
        $iterator = new GlobIterator($this->tempDir.'/**/*.css', 0, true);
        $this->assertNotEmpty(iterator_to_array($iterator));
    }

}
