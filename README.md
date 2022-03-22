[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://vshymanskyy.github.io/StandWithUkraine)

# PHP Api Client for Prismic.io

[![Build Status](https://github.com/netglue/prismic-client/workflows/Continuous%20Integration/badge.svg)](https://github.com/netglue/prismic-client/actions?query=workflow%3A"Continuous+Integration") 
[![codecov](https://codecov.io/gh/netglue/prismic-client/branch/main/graph/badge.svg)](https://codecov.io/gh/netglue/prismic-client)
[![Psalm Type Coverage](https://shepherd.dev/github/netglue/prismic-client/coverage.svg)](https://shepherd.dev/github/netglue/prismic-client)
[![Latest Stable Version](https://poser.pugx.org/netglue/prismic-client/v/stable)](https://packagist.org/packages/netglue/prismic-client)
[![Total Downloads](https://poser.pugx.org/netglue/prismic-client/downloads)](https://packagist.org/packages/netglue/prismic-client)

This is an unofficial PHP client for the [Prismic.io](https://prismic.io) headless CMS. In order to keep things simple, this library only supports version 2 of the api, so you'll have to make sure to specify your repository url in the format `https://my-repo.prismic.io/api/v2`

There is practically zero backwards compatibility with other prismic clients so this is in no way a drop-in replacement. That said, you should find that much of it remains _similar_.

## Features

* Uses a [PSR-18](https://www.php-fig.org/psr/psr-18) HTTP client for communicating with the API, so there is no dependency on a particular client implementation. The library depends on you either manually providing an HTTP client or falls back on [HTTPlug Discovery](https://github.com/php-http/discovery) to figure out an already available client.
* **Optional caching**. You can choose to cache using an HTTP client that can cache responses for you [like this little beauty](https://github.com/php-http/cache-plugin), or provide a PSR cache pool to the named constructor.
* Helpful methods in the primary interface to retrieve next/previous paginated result sets or merge all paginated results to a single result set.
* Predictable and consistent exceptions to help you recover gracefully from error conditions.
* Iterable and filterable collections to represent Slices, groups and RichText making it trivial to locate particular types of content.
* Completely separate and replaceable HTML serialisation. In fact, the content objects do not have `atHtml()` or `asText()` methods at all. There is a shipped HTML serializer which is invokable, but you might not want to use it all, instead preferring to work with your documents in your view layer directly.
* Easily implement your own result set and your own document types, replacing the default shipped implementations. Got a 'Case Study' type? Hydrate your documents to `YourModel\CaseStudy` objects so that you can build a robust content model to use in your views and elsewhere.
* Much less nullability… All collections guarantee a return type of `Prismic\Fragment` which you can more easily test for a specific type or for its _emptiness_.

## Limitations

* Only supports V2 of the api as previously mentioned
* No support for experiments _(A/B Tests)_ because this feature is out-of-order at Prismic itself, it didn't make sense to implement a feature that can't be used _(Sad face)_ - that said, if A/B tests ever become a reality again in the future, it'll be top of the pile.
* No caching out-of-the-box _(Also see "Features")_

## Installation

Install with composer: `composer require netglue/prismic-client` 

You will also require a PSR-18 HTTP client implementation, of which there are many, for example:

* [HTTPlug Clients](http://docs.php-http.org/en/latest/clients.html)
* [PSR-18 on Packagist](https://packagist.org/providers/psr/http-client-implementation)

To use the curl adapter from `php-http`, issue a `composer require php-http/curl-client`

## Documentation

Docs are a work in progress and are hosted in a public prismic repository _(obviously!)_

You can view the docs by cloning the library, cd to the source and issue a `composer install` followed by a `composer serve`. This will start up PHP's built-in server on [http://127.0.0.1:8080](http://127.0.0.1:8080) showing the docs.

#### Samples & Examples

Take a look around in the `./samples` directory; that's where you’ll find the document explorer used for rendering the documentation and examples for setting up hydrating result sets, link resolver implementations and other stuff.

It's worth mentioning that the document explorer also supports previews meaning you can add `http://127.0.0.1/preview` as a preview target in your Prismic repository and preview live changes.

## Tests

Once you have the library cloned and dependencies installed, you can run the unit tests. Smoke tests will be skipped by default, but you can run them against your own content repositories by either writing a configuration file in `test/config/config.php` _(You'll find an example in there too)_ or by setting a couple of environment variables in order to run smoke tests against a single repo:

```bash
export PRISMIC_REPO="https://my-repo.prismic.io/api/v2"
export PRISMIC_TOKEN="Some access token or not"
vendor/bin/phpunit
```

## Other Clients

The official kit can be found at [prismicio/php-kit](https://github.com/prismicio/php-kit).

Another, abandoned fork of the official kit can be found at [netglue/prismic-php-kit](https://github.com/netglue/prismic-php-kit). The reason for abandoning the fork there was the desire to start from a clean slate and make use of the recent PSRs for HTTP factories and clients, completely separate the HTML serialisation process from the content model and provide more flexible ways of hydrating your content model to objects in your domain _(Or not as the case may be!)_.

## License

[MIT Licensed](./LICENSE.md).
