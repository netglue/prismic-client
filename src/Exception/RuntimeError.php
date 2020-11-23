<?php

declare(strict_types=1);

namespace Prismic\Exception;

use RuntimeException;

class RuntimeError extends RuntimeException implements PrismicError
{
}
