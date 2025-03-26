<?php

declare(strict_types=1);

namespace Prismic\Exception;

use RuntimeException;

/** @final This class will become hard-final in the next major (2.0) */
class RuntimeError extends RuntimeException implements PrismicError
{
}
