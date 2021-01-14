# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.2.0] - 2020-01-14
### Added

* Support for PHP 8.x

## [4.1.0] - 2015-12-29

### Added
* added flag `Glob::FILTER_VALUE` for `Glob::filter()`
* added flag `Glob::FILTER_KEY` for `Glob::filter()`

## [4.0.0] - 2015-12-28

### Added

* added argument `$delimiter` to `Glob::toRegEx()`

### Changed

* switched to a better-performing algorithm for `Glob::toRegEx()`
* switched to a better-performing algorithm for `Glob::getStaticPrefix()`

### Removed

* removed `Glob::ESCAPE` flag - escaping is now always enabled
* removed `Symbol` class

## [3.3.1] - 2015-12-23

### Changed

* checked return value of `glob()`

## [3.3.0] - 2015-12-23

### Added

* added support for character ranges `[a-c]`

### Changed

* improved globbing performance by falling back to PHP's `glob()` function
  whenever possible

## [3.2.0] - 2015-12-23

### Added

* added support for `?` which matches any character
* added support for character classes `[abc]` which match any of the specified
  characters
* added support for inverted character classes `[^abc]` which match any but
  the specified characters

## [3.1.1] - 2015-08-24

### Fixed

* fixed minimum versions in composer.json

## 3.1.0 - 2015-08-21

### Added
* added `TestUtil` class

### Fixed

* fixed normalizing of slashes on Windows

## 3.0.0 - 2015-08-11

### Changed

* `RecursiveDirectoryIterator` now inherits from `\RecursiveDirectoryIterator`
  for performance reasons. Support for `seek()` was removed on PHP versions
  < 5.5.23 or < 5.6.7
* made `Glob` final

## 2.0.1 - 2015-05-21

### Changed

* upgraded to webmozart/path-util 2.0

## 2.0.0 - 2015-04-06

### Added

* added support for stream wrappers

### Changed

* restricted `**` to be used within two separators only: `/**/`. This improves
  performance while maintaining equal expressiveness


## 1.0.0 - 2015-03-19

### Added

* added support for sets: `{ab,cd}`

## 1.0.0-beta3 - 2015-01-30

### Fixed

* fixed installation on Windows

## 1.0.0-beta2 - 2015-01-22

### Added
* implemented Ant-like globbing: `*` does not match directory separators
  anymore, but `**` does
* escaping must now be explicitly enabled by passing the flag `Glob::ESCAPE`
  to any of the `Glob` methods

### Fixed
* fixed: replaced fatal error by `InvalidArgumentException` when globs are
  not absolute

## 1.0.0-beta - 2015-01-12

### Added

* first release
