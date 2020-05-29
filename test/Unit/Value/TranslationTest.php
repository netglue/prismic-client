<?php
declare(strict_types=1);

namespace PrismicTest\Value;

use Prismic\Value\Translation;
use PrismicTest\Framework\TestCase;

class TranslationTest extends TestCase
{
    public function testBasicAccessors() : void
    {
        $t = Translation::new('id', 'uid', 'type', 'lang');
        $this->assertSame('id', $t->documentId());
        $this->assertSame('uid', $t->documentUid());
        $this->assertSame('type', $t->documentType());
        $this->assertSame('lang', $t->language());
    }
}
