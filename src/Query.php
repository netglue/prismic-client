<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Value\FormSpec;
use Prismic\Value\Ref;
use function array_filter;
use function array_map;
use function http_build_query;
use function implode;
use function sprintf;

class Query
{
    /** @var FormSpec */
    private $form;

    /** @var string[]|int[] */
    private $parameters;

    public function __construct(FormSpec $form)
    {
        $this->form = $form;
        $this->parameters = $this->defaultParameters();
    }

    public function toUrl() : string
    {
        /**
         * @TODO Api URLs that already include ?integrationfield=whatever in the base URL will be broken
         *       by blindly setting BASE_URL?QUERY - Query must be merged.
         *       Furthermore, once set() handles multiple values, we will need to remove the integer keys
         *       introduced by http_build_query, i.e. ?q[0]=first&q[1]=second needs to become
         *       ?q=first&q=second
         */
        return sprintf(
            '%s?%s',
            $this->form->action(),
            http_build_query($this->parameters)
        );
    }

    /** @param int|string $value */
    public function set(string $key, $value) : self
    {
        /**
         * @TODO: Multiple values are not currently supported by this.
         *        Consider a form with a default 'q=[whatever]'. Setting 'q' will obliterate that default.
         */
        $field = $this->form->field($key);
        $field->validateValue($value);
        $parameters = $this->parameters;
        $parameters[$key] = $value;

        return $this->withParameters($parameters);
    }

    /** @param int[]|string[] $parameters */
    private function withParameters(array $parameters) : self
    {
        $clone = new static($this->form);
        $clone->parameters = $parameters;

        return $clone;
    }

    /** @return string[]|int[] */
    private function defaultParameters() : iterable
    {
        $parameters = [];
        foreach ($this->form as $field) {
            if (! $field->defaultValue()) {
                continue;
            }

            $parameters[$field->name()] = $field->defaultValue();
        }

        return $parameters;
    }

    /**
     * Limit document count per page.
     *
     * The default is 20 per page and the maximum is 100
     */
    public function resultsPerPage(int $count) : self
    {
        return $this->set('pageSize', $count);
    }

    /**
     * Set the result page to retrieve.
     */
    public function page(int $page) : self
    {
        return $this->set('page', $page);
    }

    /**
     * Restrict results to documents in the given language, or "*" for any language
     *
     * If the language is unspecified, by default results will only include documents that are found in the
     * default language for your repository.
     */
    public function lang(string $lang) : self
    {
        return $this->set('lang', $lang);
    }

    /**
     * Set the after parameter: the id of the document to start the results from (excluding that document).
     */
    public function after(string $documentId) : self
    {
        return $this->set('after', $documentId);
    }

    /**
     * Restrict the fields to retrieve in document results
     *
     * Pass multiple string arguments or an array of strings where each string represents a path to a document
     * field in the format "my-type.my-field"
     *
     * Note that paths are not prefixed with "my." like they are for predicates
     */
    public function fetch(string ...$fields) : self
    {
        return $this->set('fetch', implode(',', $fields));
    }

    /**
     * Retrieve additional fields from linked documents
     *
     * Pass multiple string arguments or an array of strings. Each string value represents a field/path on the
     * fetched (linked) document and should be specified in the format of "my-type.my-field"
     *
     * Note that paths are not prefixed with "my." like they are for predicates
     */
    public function fetchLinks(string ...$fields) : self
    {
        return $this->set('fetchLinks', implode(',', $fields));
    }

    /**
     * Set the repository ref to query at.
     *
     * By default, the ref is not set, but it is a required parameter.
     * Failing to set the ref will result in a 400 error from the Rest Api
     */
    public function ref(Ref $ref) : self
    {
        return $this->set('ref', (string) $ref);
    }

    /**
     * Order results
     *
     * By default, document order is 'undefined'. The array of strings or string arguments provided must be
     * document properties or custom type paths prefixed with 'my.' For example 'document.first_publication_date'
     * or 'my.custom-type.field-name' you can specify as many as you like.
     * The default direction is ascending.
     * To sort descending append the "desc" keyword like this: "my.my-type.field-name desc"
     */
    public function order(string ...$fields) : self
    {
        $fields = array_filter($fields);
        if (empty($fields)) {
            $parameters = $this->parameters;
            unset($parameters['orderings']);

            return $this->withParameters($parameters);
        }

        return $this->set('orderings', sprintf('[%s]', implode(',', $fields)));
    }

    /**
     * Provide query parameters.
     *
     * Pass in either a single or multiple predicate arguments, or an array of predicates.
     * You can also provide an empty array to remove the existing predicates.
     */
    public function query(Predicate ...$predicates) : self
    {
        $predicates = array_filter($predicates);
        if (empty($predicates)) {
            /**
             * @TODO This will break default q in custom forms.
             *       We should decide whether an empty query appends to the existing, or resets to the
             *       form default.
             */
            $parameters = $this->parameters;
            unset($parameters['q']);

            return $this->withParameters($parameters);
        }

        $query = '[' . implode('', array_map(static function (Predicate $predicate) : string {
            return $predicate->q();
        }, $predicates)) . ']';

        return $this->set('q', $query);
    }
}
