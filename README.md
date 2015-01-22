Webmozart Glob
==============

[![Build Status](https://travis-ci.org/webmozart/glob.svg?branch=master)](https://travis-ci.org/webmozart/glob)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webmozart/glob/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webmozart/glob/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/05213817-ed84-4171-88f5-6b818179fbe2/mini.png)](https://insight.sensiolabs.com/projects/05213817-ed84-4171-88f5-6b818179fbe2)
[![Latest Stable Version](https://poser.pugx.org/webmozart/glob/v/stable.svg)](https://packagist.org/packages/webmozart/glob)
[![Total Downloads](https://poser.pugx.org/webmozart/glob/downloads.svg)](https://packagist.org/packages/webmozart/glob)
[![Dependency Status](https://www.versioneye.com/php/webmozart:glob/1.0.0/badge.svg)](https://www.versioneye.com/php/webmozart:glob/1.0.0)

Latest release: [1.0.0-beta](https://packagist.org/packages/webmozart/glob#1.0.0-beta)

A utility implementing Ant-like globbing. Wildcards (`*`) in the glob match any
number of characters (zero or more), except directory separators (`/`). Double
wildcards (`**`) in the glob match directory separators too.

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

**Note about Windows Compatibility**

Globs need to be passed in [canonical form] with forward slashes only.

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
[canonical form]: https://webmozart.github.io/path-util/api/latest/class-Webmozart.PathUtil.Path.html#_canonicalize
[`Glob`]: src/Glob.php
[`GlobIterator`]: src/Iterator/GlobIterator.php
[`GlobFilterIterator`]: src/Iterator/GlobFilterIterator.php
