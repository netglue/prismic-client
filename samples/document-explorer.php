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
    use function getenv;
    use function implode;
    use function sprintf;
    use const PHP_EOL;

    /**
     * Provide the correct URL of your repository, and optionally,
     * a permanent access token if your API visibility is set to private.
     */
    const PRISMIC_CONFIG = [
        'api'   => 'https://your-repository-name.cdn.prismic.io/api/v2',
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
                exit;
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
                    <div class="container">
                        <h1 class="display-4">Document ID# %s</h1>
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
                <div class="container">
                    <div class="row">
                        <div class="col-md-8 border border-info">
                            %s
                        </div>
                        <div class="col-md-4">
                            %s
                        </div>
                    </div>
                </div>
                BODY,
                ($this->serializer)($this->document->body()),
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

        private function listRecentDocs(int $count = 10) : string
        {
            $markup = [];
            $markup[] = '<div class="list-group mb-4">';
            $markup[] = '<div class="list-group-item list-group-item-dark"><h5 class="my-0">Recent Documents</h5></div>';
            foreach ($this->recentDocs($count) as $link) {
                $markup[] = sprintf(
                    '<a class="list-group-item list-group-item-action d-flex w-100 justify-content-between %4$s" href="/?id=%1$s"><small>%2$s</small> <span class="badge badge-primary badge-pill align-self-end">%3$s</span></a>',
                    $link->id(),
                    $link->uid() ?? $link->id(),
                    $link->type(),
                    $this->document->id() === $link->id() ? 'active' : ''
                );
            }
            $markup[] = '</div>';

            return implode(PHP_EOL, $markup);
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
                        img {
                            max-width: 100%;
                            height: auto;
                        }    
                    </style>
                </head>
                <body>
                HEADER;
        }

        public function footer() : string
        {
            return '</body></html>';
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
