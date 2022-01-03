<?php

declare(strict_types=1);

namespace Prismic;

use Prismic\Value\FormSpec;
use Prismic\Value\Ref;

use function array_filter;
use function array_map;
use function array_merge;
use function implode;
use function is_array;
use function is_scalar;
use function sprintf;
use function strpos;
use function urlencode;

/**
 * @psalm-type QueryParams = array<string, string|int|list<string>|null>
 */
class Query
{
    /** @var FormSpec */
    private $form;

    /**
     * @var array<string, mixed>
     * @psalm-var QueryParams
     */
    private $parameters;

    public function __construct(FormSpec $form)
    {
        $this->form = $form;
        $this->parameters = [];
    }

    public function toUrl(): string
    {
        $uri = $this->form->action();
        $hasQuery = (strpos($uri, '?') !== false);
        $uri .= $hasQuery ? '&' : '?';

        return $uri . $this->buildQuery();
    }

    /** @psalm-return QueryParams */
    private function mergeWithDefaults(): array
    {
        $parameters = $this->defaultParameters();
        foreach ($this->parameters as $name => $value) {
            if (is_scalar($value)) {
                $parameters[$name] = $value;
                continue;
            }

            $merged = isset($parameters[$name]) && is_array($parameters[$name])
                ? array_merge($parameters[$name], (array) $value)
                : $value;

            $parameters[$name] = $merged;
        }

        return $parameters;
    }

    private function buildQuery(): string
    {
        /** @psalm-suppress MissingClosureParamType */
        $flatten = static function (string $name, $params): string {
            $query = [];
            /** @psalm-var scalar[] $params */
            $params = is_scalar($params) ? [$params] : $params;
            foreach ($params as $param) {
                $query[] = sprintf('%s=%s', $name, urlencode((string) $param));
            }

            return implode('&', $query);
        };

        $query = [];
        foreach ($this->mergeWithDefaults() as $name => $value) {
            $query[] = $flatten($name, $value);
        }

        return implode('&', $query);
    }

    /** @param int|string $value */
    public function set(string $key, $value): self
    {
        $field = $this->form->field($key);
        $field->validateValue($value);
        $parameters = $this->parameters;

        if ($field->isMultiple()) {
            $parameters[$key] = isset($parameters[$key]) && is_array($parameters[$key]) ? $parameters[$key] : [];
            $parameters[$key][] = $value;
        } else {
            $parameters[$key] = $value;
        }

        /** @psalm-var QueryParams $parameters */

        return $this->withParameters($parameters);
    }

    /** @psalm-param QueryParams $parameters */
    private function withParameters(array $parameters): self
    {
        $clone = new self($this->form);
        $clone->parameters = $parameters;

        return $clone;
    }

    /** @psalm-return QueryParams */
    private function defaultParameters(): array
    {
        $parameters = [];
        foreach ($this->form as $field) {
            if (! $field->defaultValue()) {
                continue;
            }

            $key = $field->name();

            if (! isset($parameters[$key])) {
                $parameters[$key] = $field->isMultiple() ? [] : null;
            }

            if ($field->isMultiple()) {
                $parameters[$key][] = $field->defaultValue();
            } else {
                $parameters[$key] = $field->defaultValue();
            }
        }

        return $parameters;
    }

    /**
     * Limit document count per page.
     *
     * The default is 20 per page and the maximum is 100
     */
    public function resultsPerPage(int $count): self
    {
        return $this->set('pageSize', $count);
    }

    /**
     * Set the result page to retrieve.
     */
    public function page(int $page): self
    {
        return $this->set('page', $page);
    }

    /**
     * Restrict results to documents in the given language, or "*" for any language
     *
     * If the language is unspecified, by default results will only include documents that are found in the
     * default language for your repository.
     */
    public function lang(string $lang): self
    {
        return $this->set('lang', $lang);
    }

    /**
     * Set the after parameter: the id of the document to start the results from (excluding that document).
     */
    public function after(string $documentId): self
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
    public function fetch(string ...$fields): self
    {
        $fields = array_filter($fields);
        if (empty($fields)) {
            $parameters = $this->parameters;
            unset($parameters['fetch']);

            return $this->withParameters($parameters);
        }

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
    public function fetchLinks(string ...$fields): self
    {
        $fields = array_filter($fields);
        if (empty($fields)) {
            $parameters = $this->parameters;
            unset($parameters['fetchLinks']);

            return $this->withParameters($parameters);
        }

        return $this->set('fetchLinks', implode(',', $fields));
    }

    /**
     * Set the repository ref to query at.
     *
     * By default, the ref is not set, but it is a required parameter.
     * Failing to set the ref will result in a 400 error from the Rest Api
     */
    public function ref(Ref $ref): self
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
    public function order(string ...$fields): self
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
    public function query(Predicate ...$predicates): self
    {
        $predicates = array_filter($predicates);
        if (empty($predicates)) {
            $parameters = $this->parameters;
            unset($parameters['q']);

            return $this->withParameters($parameters);
        }

        $query = '[' . implode('', array_map(static function (Predicate $predicate): string {
            return $predicate->q();
        }, $predicates)) . ']';

        return $this->set('q', $query);
    }
}
