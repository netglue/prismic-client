<?php
declare(strict_types=1);

namespace Prismic\Example\CustomHydratingResultSet;

use Prismic\Json;
use Prismic\ResultSet;
use Prismic\ResultSet\TypicalResultSetBehaviour;
use Prismic\Value\DataAssertionBehaviour;
use Prismic\Value\DocumentData;
use Psr\Http\Message\ResponseInterface;
use function array_merge;
use function max;

class MyResultSet implements ResultSet
{
    /**
     * Import the trait providing common, required methods for pagination etc.
     */
    use TypicalResultSetBehaviour;
    use DataAssertionBehaviour;

    /** @var string[] */
    private static $documentTypeMap = [
        'prismic-type' => CustomDocumentType::class,
    ];

    public static function withHttpResponse(ResponseInterface $response) : ResultSet
    {
        /** Decode the response body */
        $data = Json::decodeObject((string) $response->getBody());

        /** Assign pagination properties and general response information */
        $resultSet = new static();
        $resultSet->setPropertiesWithJsonObject($data);

        /** Iterate over the results and construct the appropriate document type */
        foreach ($data->results as $documentData) {
            $content = DocumentData::factory($documentData);
            if (! isset(self::$documentTypeMap[$content->type()])) {
                $resultSet->results[] = $content;
                continue;
            }

            $class = self::$documentTypeMap[$content->type()];

            $resultSet->results[] = new $class($content);
        }

        return $resultSet;
    }

    private function setPropertiesWithJsonObject(object $data) : void
    {
        $this->page = self::assertObjectPropertyIsInteger($data, 'page');
        $this->perPage = self::assertObjectPropertyIsInteger($data, 'results_per_page');
        $this->totalResults = self::assertObjectPropertyIsInteger($data, 'total_results_size');
        $this->pageCount = self::assertObjectPropertyIsInteger($data, 'total_pages');
        $this->nextPage = self::optionalStringProperty($data, 'next_page');
        $this->prevPage = self::optionalStringProperty($data, 'prev_page');
    }

    public function merge(ResultSet $with) : ResultSet
    {
        $results = array_merge($this->results, $with->results());

        $resultSet = new static();
        $resultSet->page = 1;
        $resultSet->perPage = count($results);
        $resultSet->totalResults = $this->totalResults;
        $resultSet->pageCount = max($this->pageCount - 1, 1);
        $resultSet->nextPage = $with->nextPage();
        $resultSet->prevPage = $this->prevPage;
        $resultSet->results = $results;

        return $resultSet;
    }
}
