<?php
declare(strict_types=1);

namespace PrismicTest\Document\Fragment;

use Prismic\Document\Fragment\Factory;
use Prismic\Document\Fragment\Image;
use Prismic\Document\FragmentCollection;
use Prismic\Exception\ImageViewNotFound;
use Prismic\Json;
use PrismicTest\Framework\TestCase;
use function assert;

class ImageTest extends TestCase
{
    /** @var FragmentCollection */
    private $collection;

    protected function setUp() : void
    {
        parent::setUp();
        $this->collection = Factory::factory(Json::decodeObject($this->jsonFixtureByFileName('images.json')));
        assert($this->collection instanceof FragmentCollection);
    }

    private function singleImage() : Image
    {
        $image = $this->collection->get('single_image');
        assert($image instanceof Image);

        return $image;
    }

    public function testBasicAccessors() : void
    {
        $image = $this->singleImage();
        $this->assertSame('ALT TAG', $image->alt());
        $this->assertSame('Copyright Info', $image->copyright());
        $this->assertSame('https://example.com/image.gif', $image->url());
        $this->assertSame(800, $image->width());
        $this->assertSame(600, $image->height());
        $this->assertNull($image->linkTo());
    }

    public function testThatAMainViewIsAvailableForImagesWithoutAnyViews() : void
    {
        $image = $this->singleImage();
        $this->assertSame('main', $image->viewName());
        $this->assertContainsEquals('main', $image->knownViews());
        $this->assertSame($image, $image->getView('main'));
    }

    public function testAttemptingToFetchAnUnknownImageViewIsExceptional() : void
    {
        $image = $this->singleImage();
        $this->expectException(ImageViewNotFound::class);
        $image->getView('whatever');
    }

    public function testThatImagesAreCountable() : void
    {
        $this->assertCount(1, $this->singleImage());
    }

    public function testThatImagesAreIterable() : void
    {
        foreach ($this->singleImage() as $view) {
            $this->assertInstanceOf(Image::class, $view);
        }
    }
}
