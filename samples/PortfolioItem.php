<?php
declare(strict_types=1);
// phpcs:ignoreFile

namespace Prismic\Example;

use Prismic\Api;
use Prismic\Document;
use Prismic\Document\DocumentDataConsumer;
use Prismic\Document\Fragment;
use Prismic\Document\Fragment\DocumentLink;
use Prismic\Document\Fragment\RichText;
use Prismic\Document\Fragment\TextElement;
use Prismic\Document\Fragment\WebLink;
use Prismic\Predicate;
use Prismic\Value\DocumentData;
use RuntimeException;
use function array_slice;
use function explode;
use function implode;

/**
 * This is a contrived example of how you might model a document type in prismic to something you care about.
 *
 * Let's pretend this is a portfolio site for a web design firm and this class represents a portfolio item.
 * Because, this is an example, we'll inject the Api into the constructor so that the document is capable of
 * retrieving related content itself. You might prefer to do this somewhere else in real life.
 */
class PortfolioItem implements Document
{
    use DocumentDataConsumer;

    /** @var Api */
    private $api;

    public function __construct(DocumentData $data, Api $api)
    {
        $this->data = $data;
        $this->api = $api;
    }

    /**
     * Retrieve a document content fragment by name
     *
     * This method is just a handy shortcut
     */
    private function getFragment(string $name) : Fragment
    {
        return $this->data->body()->get($name);
    }

    /**
     * Retrieve the title of the portfolio item
     */
    public function title() : string
    {
        $fragment = $this->getFragment('title');

        if (! $fragment instanceof TextElement || $fragment->isEmpty()) {
            return 'Untitled Portfolio Item'; // Or maybe throw an exception
        }

        return $fragment->text();
    }

    /**
     * Find other portfolio items that are similar to this one.
     *
     * @return iterable|PortfolioItem[]
     */
    public function similarItems(int $relevanceThreshold = 10) : iterable
    {
        $query = $this->api->createQuery()
            ->query([
                Predicate::at('document.type', $this->type()),
                Predicate::similar($this->id(), $relevanceThreshold),
            ])
            ->order('document.last_publication_date desc');

        return $this->api->query($query);
    }

    /**
     * Return the URL of this project
     */
    public function url() : string
    {
        $link = $this->getFragment('website-address');
        if (! $link instanceof WebLink || $link->isEmpty()) {
            throw new RuntimeException('No link has been specified in the CMS for this portfolio item.');
        }

        return $link->url();
    }

    /**
     * Perhaps locate the Customer document which might be another type in your system
     */
    public function customer() :? Document
    {
        $link = $this->getFragment('customer');
        if (! $link instanceof DocumentLink || $link->type() !== 'customer-type') {
            return null;
        }

        return $this->api->findById($link->id());
    }

    /**
     * If you didn't want to inject the API into your document model, you might just return the customer ID for retrieval elsewhere
     */
    public function customerId() :? string
    {
        $link = $this->getFragment('customer');
        if (! $link instanceof DocumentLink || $link->type() !== 'customer-type') {
            return null;
        }

        return $link->id();
    }

    /**
     * Return the main body of the document
     */
    public function content() : RichText
    {
        $body = $this->getFragment('body');
        if (! $body instanceof RichText) {
            // Nothing has been written yet, maybe we're previewing?
            return RichText::new([]);
        }

        return $body;
    }

    /**
     * Craft an excerpt from the main body
     */
    public function excerpt() : string
    {
        $firstNonEmptyParagraph = $this->content()->filter(static function (Fragment $fragment) : bool {
            return $fragment instanceof TextElement && $fragment->isParagraph() && ! $fragment->isEmpty();
        })->first();

        if (! $firstNonEmptyParagraph instanceof TextElement) {
            return '';
        }

        $words = array_slice(explode(' ', $firstNonEmptyParagraph->text()), 0, 50);
        return implode(' ', $words) . '…';
    }
}
