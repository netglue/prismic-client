<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment;
use Prismic\Document\FragmentCollection;
use Traversable;

use function assert;
use function iterator_to_array;

/**
 * @template T of TextElement|ListItems
 * @template-implements FragmentCollection<T>
 */
final class RichText extends BaseCollection implements FragmentCollection
{
    /** @param iterable<array-key, Fragment> $fragments */
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
