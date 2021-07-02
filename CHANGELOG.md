# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.0.0 - 2020-07-02

### Added

- Added Psalm static analysis to the code base.

### Changed

- BC Break: Changed the return type of `\Prismic\ResultSet::getIterator()` from `iterable` to `Traversable`. This is unlikely to cause any problems if you are using the shipped `TypicalResultSetBehaviour` trait.
- BC Break: `\Prismic\Document\Fragment\Embed` is now final
- BC Break: `\Prismic\Document\Fragment\Span` is now final
- BC Break: Changed return type of `\Prismic\Value\FormSpec::getIterator()` to `Traversable`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.6.1 - 2021-06-25

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Pull [#37](https://github.com/netglue/prismic-client/pull/37) fixes an issue where GeoPoint coordinates would get decoded as integers and cause a type error.
- [#37](https://github.com/netglue/prismic-client/pull/37) also fixes passing non-strings to `htmlspecialchars` via Laminas Escaper


## 0.6.0 - 2021-03-24

### Added

- Pull [#24](https://github.com/netglue/prismic-client/pull/24): The `Stringable` interface has been added to a number of Types.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.5.0 - 2021-03-03

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- [#19](https://github.com/netglue/prismic-client/pull/19) Deprecates `ApiData::tags()`.
  The list of tags returned by the remote has been deprecated and will start returning an empty set "soon" and later on, the property will be completely removed. This pull updates code for forward compatibility but that doesn't help you to get a list of used tags in your repository 🤷‍♂️
  
    There's a [blog post about it here](https://prismic.io/blog/a-change-to-how-the-prismic-api-handles-tags).

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.4 - 2021-03-02

### Added

- Nothing.

### Changed

- [#18](https://github.com/netglue/prismic-client/pull/18) makes some inconsequential changes to CS and migrates CI to use the [ridiculously good Laminas CI matrix action](https://github.com/laminas/laminas-ci-matrix-action).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.4.3 - 2020-11-24

### Added

- Nothing.

### Changed

- Added PHP 8.0 to the build matrix and updated composer constraints to indicate PHP 8.0 support.
- Update PHPUnit config for compatibility with 9.x
- Removed coding standard customisations and fixed project wide so that CS is consistent with Doctrine/Slevomat
- Bump minimum http discovery lib to 1.11 and change usage of deprecated method `findUrlFactory` to `findUriFactory`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#16](https://github.com/netglue/prismic-client/issues/16) is fixed via [#17](https://github.com/netglue/prismic-client/pull/17) - Ignores preview cookie payloads that contain only tracking information.

## 0.4.2 - 2020-07-01

### Added

- Nothing.

### Changed

- Because oEmbeds may have a string for width and height, the accessors for these properties will return null in the case where a string is present. The original attribute value can still be retrieved.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- An oEmbed with a string width or height such as "100%" will no longer cause an exception.

## 0.4.1 - 2020-06-22

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Embeds within rich text fragments were being parsed into 2 elements - one for the type and one for the embed itself 

## 0.4.0 - 2020-06-22

### Added

- BC Break: `Prismic\ResultSet\ResultSetFactory` declares a new method `withJsonObject` that is now used to construct Result Sets internally. This is due to implementing caching in the library - keeping BC would have required the provision of a Response factory, or, including a response implementation in order to rehydrate http responses from the cache - this would have added bloat and additional dependencies.

### Changed

- The constructor `Api::get()` now accepts a `Psr\Cache\CacheItemPool` as the final argument so that the client can cache response bodies internally. It's not how I wanted things to go, but experience with trying to cache with the HTTP client alone have proven less than ideal in a number of scenarios with several approaches.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.7 - 2020-06-18

### Added

- Extra `$flags` parameter to `\Prismic\Json::encode()` to allow passing standard Json extension options to `json_encode`.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Encoding of predicate values. Turns out that escaping forward slashes in the query is unacceptable to the api.

## 0.3.6 - 2020-06-18

### Added

- Added public constant `EXPECTED_ERROR_MESSAGE` to PreviewTokenExpired exception so that it's easy to change the expected value if the API changes its response in this scenario.

### Changed

- Added some doc comments to `Predicate::similar()` and altered the threshold argument name to more clearly communicate its behaviour.
- Minor change to CS requires a space between different types of use statement.
- Allow doctrine coding standard 7.0 || 8.0 with updated local overrides.

### Deprecated

- Nothing.

### Removed

- Removed hidden dependency on Psr17 URI Factory Discovery in `\Prismic\Query`

### Fixed

- Incorrect serialisation of Predicate values, a hangover from the official api client, means that simple operations such as providing a quoted string in a fulltext search, i.e. `Predicate::fulltext('document', 'A "Quoted" string')` would yield 400 errors from the API. This is now fixed and new tests added prove it.

## 0.3.5 - 2020-06-16

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed [#5](https://github.com/netglue/prismic-client/issues/5) `Predicate::hasTag()` is now correctly implemented.

## 0.3.4 - 2020-06-15

### Added

- `Primsic\Predicate::hasTag()` which is a helpful shortcut to find documents that are tagged with a specific value. This avoids a very common problem of forgetting that `document.tags` must be given an array when you use the `at` predicate. 

### Changed

- Require version [1.8](https://github.com/php-http/discovery/releases/tag/1.8.0) for HTTP Plug discovery lib, altering API unit test to not use reflection to [retrieve default strategies](https://github.com/php-http/discovery/pull/172).

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.3 - 2020-06-15

### Added

- Added `count()` method to TypicalResultSetBehaviour`.

### Changed

- ResultSet interface now extends countable ensuring that result sets are countable.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.2 - 2020-06-12

### Added

- `Prismic\Exception\PreviewTokenExpired` to indicate that the given preview token is considered expired by the remote api.

### Changed

- Documented `Api::previewSession()` with additional exception information.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.1 - 2020-06-12

### Added

- Nothing.

### Changed

- Changed and documented the exception type thrown in `Api::previewSession()` to `InvalidPreviewToken` and also made sure a completely invalid url also throws the same type when for example a token causes an exception in the underlying uri factory.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.3.0 - 2020-06-12

### Added

- Nothing.

### Changed

- Changed the `Api::get()` method so that it wraps exceptions caused by failures to locate HTTP related dependencies in `Prismic\Exception\PrismicError` exceptions. This allows library consumers to simplify exception handling.
- Predicates can now be safely rehydrated in a round trip using `eval` and `var_export` which is useful if you want to store predicates as configuration.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2020-06-04

### Added

- [#4](https://github.com/netglue/prismic-client/pull/4) Adds the `Prismic\ApiClient` interface. Whilst there's only 1 implementation here, it makes it easier to stub the api out in tests if consumers have an interface to type hint on rather than an implementation.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#3](https://github.com/netglue/prismic-client/pull/3) `\Prismic\Document::slugs()`, `\Prismic\Document::slug()` and references and methods elsewhere to "slugs". The [feature is deprecated](https://user-guides.prismic.io/en/articles/1794385-what-are-slugs) and replaced with UIDs so there is little point in keeping support for them hanging around in this lib.

### Fixed

- Nothing.

## 0.1.0 - 2020-06-03

### Added

- Added `first()` and `last()` to the collection contract.

### Changed

- Method `Prismic\Value\DocumentData::body()` changed to `Prismic\Value\DocumentData::content()`

### Deprecated

- Nothing.

### Removed

- `ArrayAccess` style methods `offsetExists` and `offsetGet` removed from `FragmentCollection`. The collection will not implement `ArrayAccess` so stay with tradition and use get and has exclusively.

### Fixed

- Fixed: numerically indexed collections were missing elements because internal arrays were receiving keys that evaluated to empty strings.

## 0.1.0-beta - 2020-06-02

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
