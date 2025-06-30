<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\FragmentCollection;

/**
 * @template T of TextElement
 * @template-implements FragmentCollection<T>
 */
abstract class ListItems extends BaseCollection implements FragmentCollection
{
}
