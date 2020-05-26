<?php
declare(strict_types=1);

namespace Prismic;

interface UrlLink extends Link
{
    public function url() : string;
}
