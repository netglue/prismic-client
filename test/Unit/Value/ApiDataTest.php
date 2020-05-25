<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Json;
use Prismic\Value\ApiData;
use PrismicTest\Framework\TestCase;

class ApiDataTest extends TestCase
{
    /** @var string */
    private $payload;

    protected function setUp() : void
    {
        parent::setUp();
        $this->payload = $this->jsonFixtureByFileName('api-data.json');
    }

    public function testApiData() : void
    {
        $data = ApiData::factory(Json::decodeObject($this->payload));
        $this->addToAssertionCount(1);
    }
}
