<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Value\FormSpec;

class Query
{
    /** @var FormSpec */
    private $form;

    public function __construct(FormSpec $form)
    {
        $this->form = $form;
    }

    public function set(string $key, $value) : self
    {
        $field = $this->form->field($key);
        $field->validateValue($value);

    }
}
