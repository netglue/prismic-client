<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Traversable;
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
        $firstIndex = null;
        foreach ($fragments as $index => $fragment) {
            if (! $fragment instanceof TextElement || ! $fragment->isListItem()) {
                $currentType = null;
                $collection = null;
                $firstIndex = null;
                continue;
            }

            if ($currentType !== $fragment->type()) {
                $fragments[$index] = $fragment->isUnorderedListItem()
                    ? new UnorderedList([$fragment])
                    : new OrderedList([$fragment]);
                $currentType = $fragment->type();
                $firstIndex = $index;
                continue;
            }

            assert($fragments[$firstIndex] instanceof ListItems);
            $fragments[$firstIndex]->addFragment($fragment);
            unset($fragments[$index]);
        }

        parent::__construct($fragments);
    }
}
