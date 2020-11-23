<?php

declare(strict_types=1);

namespace PrismicTest\Framework;

use Exception;
use Psr\Cache\InvalidArgumentException;

class CacheKeyInvalid extends Exception implements InvalidArgumentException
{
}
