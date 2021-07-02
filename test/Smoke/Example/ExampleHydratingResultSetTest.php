<?php

declare(strict_types=1);

namespace PrismicSmokeTest\Example;

use Generator;
use Prismic\Api;
use Prismic\Example\CustomHydratingResultSet\CustomDocumentType;
use Prismic\Example\CustomHydratingResultSet\MyResultSet;
use Prismic\Example\CustomHydratingResultSet\MyResultSetFactory;
use PrismicSmokeTest\TestCase;

class ExampleHydratingResultSetTest extends TestCase
{
    /** @return Generator<string, array{0:Api}> */
    public function hydratingApiProvider(): Generator
    {
        foreach ($this->compileEndPoints() as $url => $token) {
            $api = Api::get($url, $token, $this->httpClient());
            $typeMap = [];
            foreach ($api->data()->types() as $type) {
                $typeMap[$type->id()] = CustomDocumentType::class;
            }

            $factory = new MyResultSetFactory($typeMap);

            yield $api->host() => [
                Api::get($url, $token, $this->httpClient(), null, null, $factory),
            ];
        }
    }

    /** @dataProvider hydratingApiProvider */
    public function testBasicFunctionalityIsSane(Api $api): void
    {
        $query = $api->createQuery()->resultsPerPage(1);
        $resultSet = $api->query($query);
        $this->assertInstanceOf(MyResultSet::class, $resultSet);
        $this->assertContainsOnlyInstancesOf(CustomDocumentType::class, $resultSet);
    }
}
