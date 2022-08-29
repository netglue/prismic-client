<?php

declare(strict_types=1);

namespace Prismic\Exception;

use Prismic\Value\FormSpec;

use function sprintf;

final class UnknownFormField extends InvalidArgument
{
    public static function withOffendingKey(FormSpec $form, string $key): self
    {
        return new self(sprintf(
            'There is no field with the name %s in the form %s',
            $key,
            $form->id(),
        ));
    }
}
