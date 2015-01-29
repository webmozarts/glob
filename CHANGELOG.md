Changelog
=========

* 1.0.0-next (@release_date@)

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
