Changelog
=========

* 2.0.1 (2015-05-21)

 * upgraded to webmozart/path-util 2.0

* 2.0.0 (2015-04-06)

 * restricted `**` to be used within two separators only: `/**/`. This improves
   performance while maintaining equal expressiveness
 * added support for stream wrappers

* 1.0.0 (2015-03-19)

 * added support for sets: `{ab,cd}`
 
* 1.0.0-beta3 (2015-01-30)

 * fixed installation on Windows

* 1.0.0-beta2 (2015-01-22)

 * implemented Ant-like globbing: `*` does not match directory separators
   anymore, but `**` does
 * escaping must now be explicitly enabled by passing the flag `Glob::ESCAPE`
   to any of the `Glob` methods
 * fixed: replaced fatal error by `InvalidArgumentException` when globs are
   not absolute

* 1.0.0-beta (2015-01-12)

 * first release
