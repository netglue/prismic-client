<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Traversable;
use function array_values;
use function assert;
use function iterator_to_array;

final class RichText extends BaseCollection
{
    /** @inheritDoc */
    protected function __construct(iterable $fragments)
    {
        $fragments = $fragments instanceof Traversable ? iterator_to_array($fragments) : $fragments;
        $currentType = null;
        $collection = null;
        foreach ($fragments as $index => $fragment) {
            if (! $fragment instanceof TextElement || ! $fragment->isListItem()) {
                $currentType = null;
                $collection = null;
                continue;
            }

            if ($currentType !== $fragment->type()) {
                $fragments[$index] = $collection = $fragment->isUnorderedListItem()
                    ? new UnorderedList([$fragment])
                    : new OrderedList([$fragment]);
                $currentType = $fragment->type();
                continue;
            }

            assert($collection instanceof ListItems);
            $collection->addFragment($fragment);
            unset($fragments[$index]);
        }

        parent::__construct(array_values($fragments));
    }
}
