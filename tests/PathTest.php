<?php

/*
 * This file was imported from the webmozart/path-util package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Glob\Tests;

use PHPUnit\Framework\TestCase;
use Webmozart\Glob\Path;

/** *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Thomas Schulz <mail@king2500.net>
 */
final class PathTest extends TestCase
{
    private $storedEnv = array();

    protected function setUp(): void
    {
        $this->storedEnv['HOME'] = getenv('HOME');
        $this->storedEnv['HOMEDRIVE'] = getenv('HOMEDRIVE');
        $this->storedEnv['HOMEPATH'] = getenv('HOMEPATH');

        putenv('HOME=/home/webmozart');
        putenv('HOMEDRIVE=');
        putenv('HOMEPATH=');
    }

    protected function tearDown(): void
    {
        putenv('HOME='.$this->storedEnv['HOME']);
        putenv('HOMEDRIVE='.$this->storedEnv['HOMEDRIVE']);
        putenv('HOMEPATH='.$this->storedEnv['HOMEPATH']);
    }

    public function provideIsAbsolutePathTests()
    {
        return array(
            array('/css/style.css', true),
            array('/', true),
            array('css/style.css', false),
            array('', false),

            array('\\css\\style.css', true),
            array('\\', true),
            array('css\\style.css', false),

            array('C:/css/style.css', true),
            array('D:/', true),

            array('E:\\css\\style.css', true),
            array('F:\\', true),

            array('phar:///css/style.css', true),
            array('phar:///', true),

            // Windows special case
            array('C:', true),

            // Not considered absolute
            array('C:css/style.css', false),
        );
    }

    /**
     * @dataProvider provideIsAbsolutePathTests
     */
    public function testIsAbsolute($path, $isAbsolute)
    {
        self::assertSame($isAbsolute, Path::isAbsolute($path));
    }
}
