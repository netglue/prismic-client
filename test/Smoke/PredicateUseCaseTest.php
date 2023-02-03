<?php

declare(strict_types=1);

namespace PrismicSmokeTest;

use Prismic\Exception\RequestFailure;
use Prismic\Predicate;

use function chr;
use function sprintf;

class PredicateUseCaseTest extends TestCase
{
    /** @return string[][] */
    public static function searchTermProvider(): iterable
    {
        return [
            'Double Quoted'      => ['"Quoted String"'],
            'Normal Terms'       => ['multiple regular terms'],
            'Angle Brackets'     => ['<something unusual>'],
            'Emoji'              => ['Search Term with Unicode 🤣'],
            'Single Quoted'      => ["'Single Quoted Terms'"],
            'Contains Tab'       => ["Stray\tTab"],
            'Contains Newline'   => ["New\nLine"],
            'Contains Backslash' => ['Back \ Slash'],
            'Contains Fwdslash'  => ['Forward / Slash'],
            'Contains Form Feed' => ["Form\fFeed"],
            'Contains CR'        => ["Carriage\rReturn"],
            'Contains Backspace' => ['Back' . chr(8) . 'Space'],
        ];
    }

    /** @dataProvider searchTermProvider */
    public function testThatFullTextSearchIsPossibleWithAVarietyOfTerms(string $term): void
    {
        foreach ($this->apiInstances() as $api) {
            $query = $api->createQuery()
                ->query(Predicate::fulltext('document', $term))
                ->resultsPerPage(1);
            try {
                $api->query($query);
                $this->expectNotToPerformAssertions();
            } catch (RequestFailure $error) {
                $this->fail(sprintf(
                    'The full text search for "%s" failed with the error message: %s',
                    $term,
                    $error->getMessage(),
                ));
            }
        }
    }
}
