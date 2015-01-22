Webmozart Glob
==============

[![Build Status](https://travis-ci.org/webmozart/glob.svg?branch=master)](https://travis-ci.org/webmozart/glob)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webmozart/glob/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webmozart/glob/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/05213817-ed84-4171-88f5-6b818179fbe2/mini.png)](https://insight.sensiolabs.com/projects/05213817-ed84-4171-88f5-6b818179fbe2)
[![Latest Stable Version](https://poser.pugx.org/webmozart/glob/v/stable.svg)](https://packagist.org/packages/webmozart/glob)
[![Total Downloads](https://poser.pugx.org/webmozart/glob/downloads.svg)](https://packagist.org/packages/webmozart/glob)
[![Dependency Status](https://www.versioneye.com/php/webmozart:glob/1.0.0/badge.svg)](https://www.versioneye.com/php/webmozart:glob/1.0.0)

Latest release: [1.0.0-beta2](https://packagist.org/packages/webmozart/glob#1.0.0-beta2)

A utility implementing Ant-like globbing. Wildcards (`*`) in the glob match any
number of characters (zero or more), except directory separators (`/`). Double
wildcards (`**`) in the glob match directory separators too.

Usage
-----

The main class of the package is [`Glob`]. Use `Glob::glob()` to glob the 
filesystem:

```php
use Webmozart\Glob\Glob;

$paths = Glob::glob('/path/to/dir/*.css'); 
```

You can also use [`GlobIterator`] to search the filesystem iteratively. However,
the iterator is not guaranteed to return sorted results:

```php
use Webmozart\Glob\Iterator\GlobIterator;

$iterator = new GlobIterator('/path/to/dir/*.css');

foreach ($iterator as $path) {
    // ...
}
```

### Path Matching

The package also provides utility methods for comparing paths against globs.
Use `Glob::match()` to match a path against a glob:

```php
if (Glob::match($path, '/path/to/dir/*.css')) {
    // ...
}
```

`Glob::filter()` filters a list of paths by a glob:

```php
$paths = Glob::filter($paths, '/path/to/dir/*.css');
```

The same can be achieved iteratively with [`GlobFilterIterator`]:

```php
use Webmozart\Glob\Iterator\GlobFilterIterator;

$iterator = new GlobFilterIterator('/path/to/dir/*.css', new ArrayIterator($paths));

foreach ($iterator as $path) {
    // ...
}
```

### Relative Globs

Relative globs such as `*.css` are not supported. Usually, such globs refer to
paths relative to the current working directory. This utility, however, does not
want to make such assumptions. Hence you should always pass absolute globs.

If you want to allow users to pass relative globs, I recommend to turn the globs
into absolute globs using the [Webmozart Path Utility]:

```php
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;

// If $glob is absolute, that glob is used without modification.
// If $glob is relative, it is turned into an absolute path based on the current
// working directory.
$paths = Glob::glob(Path::makeAbsolute($glob, getcwd());
```

### Windows Compatibility

Globs need to be passed in [canonical form] with forward slashes only.
Returned paths contain forward slashes only.

### Escaping

The `Glob` class supports a mode where you can match files that actually
contain a `*` in their filename. To enable the escape mode, pass the
`Glob::ESCAPE` flag to any of the methods in `Glob`.

```php
$paths = Glob::glob('//backup\\*/*.css', Glob::ESCAPE);
```

In the previous example, the glob matches all CSS files in the `backup*`
directory rather than in all directories starting with `backup`. Due to PHP's 
own escaping in strings, the backslash character `\` needs to be typed twice to
produce a single `\` in the string.

The following escape sequences are available:

* `\\*`: match a `*` in the path
* `\\\\`: match a `\` in the path

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Installation
------------

Use [Composer] to install the package:

```
$ composer require webmozart/glob@dev
```

Contribute
----------

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at the package's [Git repository].

Support
-------

If you are having problems, send a mail to bschussek@gmail.com or shout out to
[@webmozart] on Twitter.

License
-------

All contents of this package are licensed under the [MIT license].

[Composer]: https://getcomposer.org
[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/webmozart/glob/graphs/contributors
[issue tracker]: https://github.com/webmozart/glob/issues
[Git repository]: https://github.com/webmozart/glob
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE
[Webmozart Path Utility]: https://github.com/webmozart/path-util
[canonical form]: https://webmozart.github.io/path-util/api/latest/class-Webmozart.PathUtil.Path.html#_canonicalize
[`Glob`]: src/Glob.php
[`GlobIterator`]: src/Iterator/GlobIterator.php
[`GlobFilterIterator`]: src/Iterator/GlobFilterIterator.php
