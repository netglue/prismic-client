<?php
declare(strict_types=1);

/**
 * This is a simple script that displays the JSON structure for the selected document and a list of recently published
 * documents.
 *
 * To run it, first of all, change the PRISMIC_CONFIG constant below with the correct details for your repository and
 * then fire up a terminal and `cd` to this library's root directory and issue `composer serve`
 * This will start up PHP’s built in web server on port 8080. To see it in action, visit http://localhost:8080
 *
 * If you want to run the script on a different port, issue the following command, replacing 8080 with your chosen port
 * php -S 0.0.0.0:8080 -t samples samples/document-explorer.php
 */

namespace Prismic\Sample {

    use Prismic\Api;
    use Prismic\DefaultLinkResolver;
    use Prismic\Document;
    use Prismic\Document\Fragment\DocumentLink;
    use Prismic\Exception\PrismicError;
    use Prismic\Response;
    use Prismic\Serializer\HtmlSerializer;
    use Prismic\Value\Bookmark;
    use Prismic\Value\DocumentData;
    use function urlencode;
    use const PHP_EOL;
    use function array_filter;
    use function array_map;
    use function getenv;
    use function header;
    use function implode;
    use function sprintf;
    use function strpos;

    /**
     * Provide the correct URL of your repository, and optionally,
     * a permanent access token if your API visibility is set to private.
     */
    const PRISMIC_CONFIG = [
        'api'   => 'https://phpclient.cdn.prismic.io/api/v2',
        'token' => null,
    ];

    require_once __DIR__ . '/../vendor/autoload.php';

    class Resolver extends DefaultLinkResolver
    {
        protected function resolveDocumentLink(DocumentLink $link) :? string
        {
            return sprintf('/?id=%s', $link->id());
        }
    }

    $finder = new Finder();
    echo (string) $finder;

    class Finder
    {
        /** @var Api */
        private $api;
        /** @var Document  */
        private $document;
        /** @var HtmlSerializer */
        private $serializer;

        public function __construct()
        {
            try {
                $apiUrl = getenv('PRISMIC_API') ?: PRISMIC_CONFIG['api'];
                $accessToken = getenv('PRISMIC_TOKEN') ?: PRISMIC_CONFIG['token'];
                $this->api = Api::get($apiUrl, $accessToken);
                $document = isset($_GET['id']) ? $this->api->findById($_GET['id']) : null;
                $document = $document ?: $this->mostRecent();
                $this->document = $document;
                $this->serializer = new HtmlSerializer(new Resolver());
            } catch (PrismicError $e) {
                $this->invalidRepo($e);
            }

            if (isset($_GET['token']) && strpos($_SERVER['REQUEST_URI'], '/preview') === 0) {
                $doc = $this->api->previewSession($_GET['token']);
                $url = '/';
                if ($doc) {
                    $url = (new Resolver())->resolve($doc);
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
                ($this->serializer)($this->document->body()),
                $this->listBookmarks(),
                $this->listRecentDocs(20)
            );

            return $title . $body;
        }

        private function recentDocs(int $count = 10) : Response
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
            $bookmarks = array_filter(array_map(function (Bookmark $bookmark) :? DocumentData {
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
                getenv('PRISMIC_API') ?: PRISMIC_CONFIG['api']
            );

            return sprintf('%s</body></html>', $oldToolbar);
        }

        private function invalidRepo(PrismicError $e) : void
        {
            $markup = [];
            $markup[] = '<div class="alert alert-danger my-4"><h2>Failed to Retrieve Api Data</h2>';
            $markup[] = '<p>Check the repository URL and access token you configured before running this script.</p>';
            $markup[] = sprintf('<p><code>%s</code>', $e->getMessage());
            $markup[] = sprintf('<pre>%s</pre>', $e->getTraceAsString());
            $markup[] = '</div>';

            echo $this->header() . implode(PHP_EOL, $markup) . $this->footer();
        }
    }
}
