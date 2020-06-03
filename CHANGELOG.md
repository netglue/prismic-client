# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 0.1.0-beta2 - TBD

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
