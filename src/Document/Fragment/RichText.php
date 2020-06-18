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
        parent::__construct([]);
        $fragments = $fragments instanceof Traversable ? iterator_to_array($fragments) : $fragments;
        $currentType = null;
        $collection = null;
        foreach ($fragments as $fragment) {
            if (! $fragment instanceof TextElement || ! $fragment->isListItem()) {
                $currentType = null;
                $collection = null;
                $this->addFragment($fragment);
                continue;
            }

            if ($currentType !== $fragment->type()) {
                $collection = $fragment->isUnorderedListItem()
                    ? new UnorderedList([])
                    : new OrderedList([]);
                $collection->addFragment($fragment);
                $this->addFragment($collection);
                $currentType = $fragment->type();
                continue;
            }

            assert($collection instanceof ListItems);
            $collection->addFragment($fragment);
        }
    }
}
