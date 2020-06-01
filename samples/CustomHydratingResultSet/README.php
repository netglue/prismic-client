<?php
declare(strict_types=1);

use Prismic\Api;
use Prismic\Example\CustomHydratingResultSet\CustomDocumentType;
use Prismic\Example\CustomHydratingResultSet\MyResultSet;
use Prismic\Example\CustomHydratingResultSet\MyResultSetFactory;

$apiUrl = getenv('PRISMIC_API') ?: null;
$accessToken = getenv('PRISMIC_TOKEN') ?: null;

/**
 * Create a factory that is capable of creating result sets from HTTP responses.
 *
 * The implementation here accepts a simple map of document types to FQCNs. An implementation might have a default
 * document type for example and could be initialised by your DI container of choice…
 */
$myResultSetFactory = new MyResultSetFactory([
    'some-prismic-type' => CustomDocumentType::class,
]);

$api = Api::get($apiUrl, $accessToken, null, null, null, $myResultSetFactory);

$resultSet = $api->query($api->createQuery());

assert($resultSet instanceof MyResultSet);

/**
 * $resultSet is an instance of your own ResultSet implementation and any documents matching the type
 * 'some-prismic-type' will be instances of CustomDocumentType
 */

foreach ($resultSet as $document) {
    printf('Document #%s is an instance of %s' . PHP_EOL, $document->id(), get_class($document));
}
