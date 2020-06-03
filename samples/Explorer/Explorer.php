<?php
declare(strict_types=1);

namespace Prismic\Example\Explorer;

use Prismic\Api;
use Prismic\Document;
use Prismic\Exception\PrismicError;
use Prismic\ResultSet;
use Prismic\Serializer\HtmlSerializer;
use Prismic\Value\Bookmark;
use Prismic\Value\DocumentData;
use function array_filter;
use function array_map;
use function getenv;
use function header;
use function htmlspecialchars;
use function sprintf;
use function strpos;

class Explorer
{
    private const PRISMIC_CONFIG = [
        'api'   => 'https://phpclient.cdn.prismic.io/api/v2',
        'token' => null,
    ];

    /** @var Api */
    private $api;
    /** @var Document  */
    private $document;
    /** @var HtmlSerializer */
    private $serializer;

    public function __construct()
    {
        try {
            $apiUrl = getenv('PRISMIC_API') ?: self::PRISMIC_CONFIG['api'];
            $accessToken = getenv('PRISMIC_TOKEN') ?: self::PRISMIC_CONFIG['token'];
            $this->api = Api::get($apiUrl, $accessToken);
            $document = isset($_GET['id']) ? $this->api->findById($_GET['id']) : null;
            $document = $document ?: $this->mostRecent();
            $this->document = $document;
            $this->serializer = new HtmlSerializer(new ExplorerResolver());
        } catch (PrismicError $e) {
            $this->invalidRepo($e);
        }

        if (isset($_GET['token']) && strpos($_SERVER['REQUEST_URI'], '/preview') === 0) {
            $doc = $this->api->previewSession($_GET['token']);
            $url = '/';
            if ($doc) {
                $url = (new ExplorerResolver())->resolve($doc);
                $url = $url ?: '/';
            }
            header('Location: ' . $url);
            exit(0);
        }
    }

    public function mostRecent() :? Document
    {
        return $this->api->queryFirst(
            $this->api->createQuery()
                ->order('document.last_publication_date desc')
        );
    }

    public function __toString() : string
    {
        return $this->header() . $this->body() . $this->footer();
    }

    public function body() : string
    {
        $title = sprintf(
            <<<TITLE
                <div class="jumbotron jumbotron-fluid">
                    <div class="container-fluid">
                        <h1 class="display-4">Document ID <code>%s</code></h1>
                        <p class="lead">Document Type: <code>%s</code>. Published %s and last updated %s</p>
                    </div>
                </div>
                TITLE,
            $this->document->id(),
            $this->document->type(),
            $this->document->firstPublished()->format('jS M Y H:i'),
            $this->document->lastPublished()->format('jS M Y H:i')
        );

        $body = sprintf(
            <<<BODY
                <div class="container-fluid">
                    <div class="row m-2">
                        <div class="col-md-8 border border-info bg-light">
                            %s
                        </div>
                        <div class="col-md-4">
                            %s
                            %s
                        </div>
                    </div>
                </div>
                BODY,
            ($this->serializer)($this->document->content()),
            $this->listBookmarks(),
            $this->listRecentDocs(20)
        );

        return $title . $body;
    }

    private function recentDocs(int $count = 10) : ResultSet
    {
        return $this->api->query(
            $this->api->createQuery()
                ->order('document.last_publication_date desc')
                ->resultsPerPage($count)
        );
    }

    /** @param DocumentData[] $documents */
    private function documentList(iterable $documents, string $title, callable $anchor) : string
    {
        $list = '';
        foreach ($documents as $document) {
            $list .= sprintf(
                <<<ITEM
                    <a class="list-group-item list-group-item-action d-flex w-100 justify-content-between %s"
                        href="/?id=%s">
                        <small>%s</small>
                        <span class="badge badge-primary badge-pill align-self-end">%s</span>
                    </a>
                    ITEM,
                $this->document->id() === $document->id() ? 'active' : '',
                $document->id(),
                $anchor($document),
                $document->type(),
            );
        }

        return sprintf(
            <<<MARKUP
                <div class="list-group mb-4">
                    <div class="list-group-item list-group-item-dark"><h5 class="my-0">%s</h5></div>
                    %s
                </div>
                MARKUP,
            $title,
            $list
        );
    }

    private function listRecentDocs(int $count = 10) : string
    {
        return $this->documentList(
            $this->recentDocs($count),
            'Recent Documents',
            static function (DocumentData $data) : string {
                return $data->uid() ?: $data->id();
            }
        );
    }

    private function listBookmarks() : string
    {
        $api = $this->api;
        $bookmarks = array_filter(array_map(function (Bookmark $bookmark) :? Document {
            return $this->api->findByBookmark($bookmark->name());
        }, $this->api->data()->bookmarks()));

        return $this->documentList(
            $bookmarks,
            'Bookmarks',
            static function (DocumentData $data) use ($api) : string {
                $bookmark = $api->data()->bookmarkFromDocumentId($data->id());

                return $bookmark ? $bookmark->name() : $data->id();
            }
        );
    }

    public function header() : string
    {
        return <<<'HEADER'
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="utf-8" />
                    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                    <title>Prismic Document Browser</title>
                    <link rel="stylesheet"
                        href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css"
                        integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk"
                        crossorigin="anonymous">
                    <style>
                        body {
                            padding-bottom: 5rem;
                        }
                        img {
                            max-width: 100%;
                            height: auto;
                        }
                        pre {
                            background-color: black;
                            color: white;
                            padding: 1em;
                            border-radius: 0.3em;
                        }
                    </style>
                </head>
                <body>
                HEADER;
    }

    public function footer() : string
    {
        $newToolbar = sprintf(
            '<script async defer src="https://static.cdn.prismic.io/prismic.js?repo=%s&new=true"></script>',
            $this->api->host()
        );

        $oldToolbar = sprintf(
            <<<MARKUP
                <script>
                    window.prismic = {
                        endpoint: '%s'
                    };
                </script>
                <script type="text/javascript" src="https://static.cdn.prismic.io/prismic.min.js?new=true"></script>
                MARKUP,
            getenv('PRISMIC_API') ?: self::PRISMIC_CONFIG['api']
        );

        return sprintf('%s</body></html>', $oldToolbar);
    }

    private function invalidRepo(PrismicError $e) : void
    {
        $markup = <<<MARKUP
                <div class="container">
                    <div class="alert alert-danger my-4">
                        <h2>An exception was thrown 🤦‍♂️</h2>
                        <p>Make sure that you’ve set the environment variable for <code>PRISMIC_API</code> to the full URL
                           of your repository such as '<code>https://myrepo.prismic.io/api/v2</code>' and, if required, the
                           environment variable <code>PRISMIC_TOKEN</code> to a valid access token for your repo.</p>
                        <p>Alternatively, you can edit this file and put those values in the constant <code>PRISMIC_CONFIG</code></p>
                        <h4>Exception Message: <code>%s</code></h4>
                        <h4>Exception Trace:</h4>
                        <pre>%s</pre>
                    </div>
                </div>
                MARKUP;
        $markup = sprintf($markup, $e->getMessage(), htmlspecialchars($e->getTraceAsString()));
        echo $this->header() . $markup . $this->footer();
    }
}
