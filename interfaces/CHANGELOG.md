# Changelog

All Notable changes to `League\Uri\Interfaces` will be documented in this file

## [7.2.0](https://github.com/thephpleague/uri/compare/7.1.0...7.2.0) - 2023-08-30

### Added

- `League\Uri\Idna\Converter::isIdn`
- `League\Uri\Ipv4\Converter::isIpv4`

### Fixed

- Add support for `Stringable` host object in `League\Uri\Idna\Converter` feature.
- Add support for `Stringable` host object in `League\Uri\Exceptions\ConversionFailed` feature.

### Deprecated

- None

### Removed

- None

## [7.1.0](https://github.com/thephpleague/uri/compare/7.0.0...7.1.0) - 2023-08-21

### Added

- `League\Uri\Encoder` to normalize encoding URI and URI components.
- `League\Uri\KeyValuePair\Converter` to parse and build key/value pair string.

### Fixed

- Rewrite `QueryString` classes and fix query encoding for basic RFC3986. [#109](https://github.com/thephpleague/uri-src/issues/109)

### Deprecated

- None

### Removed

- None

## [7.0.0](https://github.com/thephpleague/uri/compare/2.3.0...7.0.0)  - 2023-08-10

### Added

- New method to `UriComponentInterface::value`
- New method to `UriComponentInterface::toString`
- New method to `UserInfoInterface::withUser`
- New method to `UserInfoInterface::withPass`
- New method to `UriInterface::toString`
- New method to `UriInterface::toComponents`
- `League\Uri\IPv4` tools
- `League\Uri\Idna` tools
- `League\Uri\UriString` parser
- `League\Uri\QueryString` parser

### Fixed

- None

### Deprecated

- None

### Removed

- Support for PHP7
- Support for `__set_state`
- `UriComponentInterface::getContent` is removed in favor of `UriComponentInterface::value`
- `UriComponentInterface::withContent` is removed with no replacement use other means to change the value of the component.
- `UserInfoInterface::withUserInfo` is removed in favor of `UserInfoInterface::withUser` and `UserInfoInterface::withPass`.
- `HostInfoInterface::labels` is removed with no replacement use the `IteratorAggregate::getIterator` method instead.
- `SegmentedPathInterface::segments` is removed with no replacement use the `IteratorAggregate::getIterator` method instead.
- `League\Uri\Idna\Idna` use `League\Uri\Idna\Converter` instead
- `League\Uri\Idna\IdnaInfo` use `League\Uri\Idna\Result` instead
- `League\Uri\Exception\IdnSupportMissing` use `League\Uri\Exception\MissingFeature` instead

## 2.3.0 - 2021-06-28

### Added

- IDNA processing classes

### Fixed

- None

### Deprecated

- None

### Removed

- Support for PHP7.1

## 2.2.0 - 2020-10-31

### Added

- Support for PHP8 thanks to of [someniatko](https://github.com/someniatko)

### Fixed

- None

### Deprecated

- None

### Removed

- None

## 2.1.0 - 2020-02-08

### Added

- `League\Uri\Exceptions\FileinfoSupportMissing` based on the work of [Nicolas Grekas](https://github.com/nicolas-grekas)

### Fixed

- Improved docblock.

### Deprecated

- None

### Removed

- None

## 2.0.1 - 2019-12-17

### Added

- Nothing

### Fixed

- Remove useless docblock from `League\Uri\Contract\IpHostInterface::withoutZoneIdentifier`

### Deprecated

- None

### Removed

- None

## 2.0.0 - 2019-10-17

### Added

- `League\Uri\Contract\AuthorityInterface`
- `League\Uri\Contract\DataPathInterface`
- `League\Uri\Contract\DomainHostInterface`
- `League\Uri\Contract\FragmentInterface`
- `League\Uri\Contract\HostInterface`
- `League\Uri\Contract\IpHostInterface`
- `League\Uri\Contract\PathInterface`
- `League\Uri\Contract\PortInterface`
- `League\Uri\Contract\QueryInterface`
- `League\Uri\Contract\SegmentedPathInterface`
- `League\Uri\Contract\UriComponentInterface`
- `League\Uri\Contract\UriException`
- `League\Uri\Contract\UriInterface`
- `League\Uri\Contract\UserInfoInterface`
- `League\Uri\Exception\EncodingNotFound`
- `League\Uri\Exception\IdnSupportMissing`
- `League\Uri\Exception\SyntaxError`

### Fixed

- None

### Deprecated

- None

### Removed

- `League\Uri\Interfaces` namespace
- `League\Uri\UriInterface`
- support for `PHP7.0`

## 1.1.1 - 2018-11-05

### Added

- None

### Fixed

- Make `League\Uri\Interfaces\Uri` implements `League\Uri\UriInterface`

### Deprecated

- None

### Removed

- None

## 1.1.0 - 2018-05-22

### Added

- `League\Uri\UriInterface`.

### Fixed

- None

### Deprecated

- `League\Uri\Interfaces\Uri` use `League\Uri\UriInterface` instead.

### Removed

- None

## 1.0.0 - 2017-01-04

### Added

- None

### Fixed

- None

### Deprecated

- None

### Removed

- `League\Uri\Interfaces\Component`. The interface is moved to the League URI Components package.

## 0.4.0 - 2016-12-09

### Added

- `League\Uri\Interfaces\Path` replaces `League\Uri\Interfaces\PathComponent`
- `League\Uri\Interfaces\Path::isEmpty`

### Fixed

- None

### Deprecated

- None

### Removed

- `League\Uri\Interfaces\CollectionComponent`
- `League\Uri\Interfaces\PathComponent`

## 0.3.0 - 2016-12-01

### Added

- `League\Uri\Interfaces\Component::NO_ENCODING` to remove any specific encoding
- `League\Uri\Interfaces\Component::RFC3986_ENCODING` to specify encoding according to RFC3986 rules
- `League\Uri\Interfaces\Component::RFC3987_ENCODING` to specify encoding according to RFC3987 rules

### Fixed

- Update `Component::getContent` optional parameter default.

### Deprecated

- None

### Removed

- `League\Uri\Interfaces\Component::RFC3986`
- `League\Uri\Interfaces\Component::RFC3987`

## 0.2.0 - 2016-11-29

### Added

- `League\Uri\Interfaces\Component::RFC3986` to specify encoding according to RFC3986 rules
- `League\Uri\Interfaces\Component::RFC3987` to specify encoding according to RFC3987 rules

### Fixed

- `League\Uri\Interfaces\Component::getContent` now takes an optional `$enc_type` parameter
to specify the returned content encoding rules.
- `League\Uri\Interfaces\Uri` docblocks simplified around Exception thrown

### Deprecated

- None

### Removed

- None

## 0.1.0 - 2016-10-17

### Added

- `League\Uri\Interfaces\Component::getContent`
- `League\Uri\Interfaces\Component::withContent`
- `League\Uri\Interfaces\Component::isDefined`

### Fixed

- Renamed `League\Uri\Interfaces\Collection` to `League\Uri\Interfaces\CollectionComponent`
- Renamed `League\Uri\Interfaces\Path` to `League\Uri\Interfaces\PathComponent`

### Deprecated

- None

### Removed

- `League\Uri\Interfaces\UriPart`
- `League\Uri\Interfaces\HierarchicalComponent`
- `League\Uri\Interfaces\Scheme`
- `League\Uri\Interfaces\User`
- `League\Uri\Interfaces\Pass`
- `League\Uri\Interfaces\UserInfo`
- `League\Uri\Interfaces\Host`
- `League\Uri\Interfaces\Port`
- `League\Uri\Interfaces\Path::withoutEmptySegments`
- `League\Uri\Interfaces\Path::hasTrailingSlash`
- `League\Uri\Interfaces\Path::withTrailingSlash`
- `League\Uri\Interfaces\Path::withoutTrailingSlash`
- `League\Uri\Interfaces\Path::getTypecode`
- `League\Uri\Interfaces\Path::withTypecode`
- `League\Uri\Interfaces\Path::FTP_TYPE_ASCII`
- `League\Uri\Interfaces\Path::FTP_TYPE_BINARY`
- `League\Uri\Interfaces\Path::FTP_TYPE_DIRECTORY`
- `League\Uri\Interfaces\Path::FTP_TYPE_EMPTY`
- `League\Uri\Interfaces\HierarchicalPath`
- `League\Uri\Interfaces\DataPath`
- `League\Uri\Interfaces\Query`
- `League\Uri\Interfaces\Fragment`
