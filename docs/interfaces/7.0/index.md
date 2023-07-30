---
layout: default
title: URI comoon interfaces and tools
redirect_from:
- /interfaces/
---

Uri Common tools
=======

This package contains:

- interface to represent URI objects and components
- parsers to parse and build URI and Query strings
- tools to help in processing URIs (IPv4 Converter and IDNA classes)

System Requirements
-------

You need **PHP >= 8.1** but the latest stable version of PHP is recommended.

In order to handle IDN host you are required to also either install the `intl`
extension or require a polyfill for the `intl` IDN functions like the
`symfony/polyfill-intl-idn` otherwise an exception will be thrown
when attempting to validate such host.

To allow IPv4 conversion you will need at least:

- the `GMP` extension and/or
- `BCMatch` extension and/or
- a `64-bits` PHP version

otherwise an exception will be thrown when attempting to normalize a host
as an IPv4 address.

Install
--------

```
$ composer require league/uri-interfaces:^7.0
```