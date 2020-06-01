<?php
declare(strict_types=1);

/**
 * This is a simple script that displays the documents in a repository with rudimentary HTML along with a navigable
 * list of bookmarks are recently published docs.
 *
 * By default, the configured repository will point to the docs for this lib which are hosted on Prismic.io.
 *
 * To run it against your own repository, set the repo url and if required the access token as environment vars
 * before starting up the cli server.
 *
 * $ cd path/to/this-library
 * $ export PRISMIC_API="https://my-repo.prismic.io/api/v2"
 * $ export PRISMIC_TOKEN="My Access Token"
 * $ composer serve
 *
 * Then visit http://localhost:8080 in your browser.
 *
 * If you want to run the script on a different port, issue the following command, replacing 8080 with your chosen port
 * php -S 0.0.0.0:8080 -t samples samples/document-explorer.php
 */

use Prismic\Example\Explorer\Explorer;

require_once __DIR__ . '/../vendor/autoload.php';

echo (string) (new Explorer());
