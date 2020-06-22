<?php
declare(strict_types=1);

namespace Prismic\Example\CustomHydratingResultSet;

use Prismic\Json;
use Prismic\ResultSet;
use Prismic\ResultSet\ResultSetFactory;
use Prismic\Value\DataAssertionBehaviour;
use Prismic\Value\DocumentData;
use Psr\Http\Message\ResponseInterface;

class MyResultSetFactory implements ResultSetFactory
{
    use DataAssertionBehaviour;

    /**
     * A Simple array that maps a prismic type to a FQCN
     *
     * @var string[]
     */
    private $typeMap;

    /** @param string[] $typeMap */
    public function __construct(array $typeMap)
    {
        $this->typeMap = $typeMap;
    }

    public function withHttpResponse(ResponseInterface $response) : ResultSet
    {
        /** Decode the response body */
        $data = Json::decodeObject((string) $response->getBody());

        return $this->withJsonObject($data);
    }

    public function withJsonObject(object $data) : ResultSet
    {
        /** Iterate over the results and construct the appropriate document type */
        $results = [];
        foreach ($data->results as $documentData) {
            $content = DocumentData::factory($documentData);
            if (! isset($this->typeMap[$content->type()])) {
                $results[] = $content;
                continue;
            }

            $class = $this->typeMap[$content->type()];

            $results[] = new $class($content);
        }

        /** Assign pagination properties and general response information */
        return new MyResultSet(
            self::assertObjectPropertyIsInteger($data, 'page'),
            self::assertObjectPropertyIsInteger($data, 'results_per_page'),
            self::assertObjectPropertyIsInteger($data, 'total_results_size'),
            self::assertObjectPropertyIsInteger($data, 'total_pages'),
            self::optionalStringProperty($data, 'next_page'),
            self::optionalStringProperty($data, 'prev_page'),
            $results
        );
    }
}
