<?php

declare(strict_types=1);

namespace Prismic\Exception;

use RuntimeException;

final class RuntimeError extends RuntimeException implements PrismicError
{
}
