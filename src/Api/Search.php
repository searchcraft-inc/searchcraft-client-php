<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;
use Searchcraft\Validators;

/**
 * Class for interacting with the Searchcraft search endpoints.
 */
class Search extends Base
{
    /**
     * Query modes accepted for string queries.
     *
     * Anything else supplied as `mode` falls back to `fuzzy`.
     *
     * @var string[]
     */
    private const QUERY_MODES = ['fuzzy', 'exact', 'dynamic'];

    /**
     * Occur modes accepted for string queries.
     *
     * @var string[]
     */
    private const OCCUR_MODES = ['should', 'must', 'mustnot'];

    /**
     * Option keys consumed while building the payload rather than copied to
     * the top level of the request verbatim.
     *
     * @var string[]
     */
    private const RESERVED_OPTIONS = [
        'limit',
        'offset',
        'order_by',
        'sort',
        'mode',
        'fields',
        'occur',
    ];

    /**
     * Perform a search query on an index.
     *
     * A string `$query` is wrapped in a single clause of the kind named by
     * `$options['mode']`. An array `$query` is forwarded to the engine
     * verbatim, which is how you reach the multi-clause and non-string query
     * kinds the engine supports: `fuzzy`, `exact`, `dynamic`, `term` (a
     * single unparsed term against one field) and `more-like-this` (whose
     * `ctx` is a document's internal `document_id`). Each clause in a
     * multi-clause query may carry `occur` of `should`, `must`, or
     * `mustnot`.
     *
     * The `occur` and `fields` options describe a single clause, so they
     * apply to string queries only. Supplying either alongside an array
     * query raises a {@see SearchcraftException} rather than being dropped —
     * set them inside the clause itself in that case.
     *
     * @param string $indexName The name of the index to search
     * @param string|array $query The search query string or complex query object
     * @param array $options Search options including:
     *   - limit: Maximum number of results to return (default: 20). Values
     *     above {@see \Searchcraft\Validators::MAX_SEARCH_LIMIT} are
     *     rejected client-side.
     *   - offset: Number of results to skip (default: 0)
     *   - order_by: Field to order results by. Ordering by a field means
     *     hits come back with a null score.
     *   - sort: Sort direction "asc" or "desc" (default: "desc")
     *   - mode: "fuzzy" (default), "exact", or "dynamic" query mode for
     *     string queries. Ignored when `$query` is an array.
     *   - occur: "should", "must", or "mustnot" for the built clause.
     *     Applies to string queries only.
     *   - fields: Restricts a string query to specific fields instead of the
     *     index's configured `search_fields`. Accepts a field name, a list
     *     of field names, or a map of field name to boost. Honoured for
     *     `fuzzy` and `term` clauses. Applies to string queries only.
     *   - minimum_number_should_match: Minimum number of optional (`should`)
     *     clauses a document must match. Added in Searchcraft Engine 0.11.0.
     *   - time_decay_field: Datetime field used to decay relevance for older
     *     documents, overriding the index's own setting for this query.
     *   - index_weighting: Relevance weight for this index within a
     *     federation search (default: 1.0).
     *
     * Unrecognised options are forwarded to the engine unchanged.
     *
     * @return array Search results. `time_taken` on the response is in
     *               seconds.
     * @throws SearchcraftException
     */
    public function query(string $indexName, $query, array $options = []): array
    {
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/index/{$indexName}/search", $params);
    }

    /**
     * Perform a federated search query across multiple indices.
     *
     * @param string $federationName The name of the federation to search
     * @param string|array $query The search query string or complex query object
     * @param array $options Same options as {@see self::query}, including
     *                       `index_weighting` to weight this search against
     *                       the federation's member indexes.
     * @return array Search results from all indices in the federation
     * @throws SearchcraftException
     */
    public function federatedQuery(string $federationName, $query, array $options = []): array
    {
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->request('POST', "/federation/{$federationName}/search", $params);
    }

    /**
     * Request a streaming AI-generated summary for search results on an index.
     *
     * The engine streams Server-Sent Events (SSE) — an initial `metadata`
     * event with `results_count` and `cached` flags, zero or more `delta`
     * events each containing a `content` chunk of the summary text, and a
     * final `done` event. When the generator errors an `error` event is
     * emitted instead. The index must have `ai_enabled: true` and an
     * AI configuration with a configured `search_summary` section.
     *
     * Added in Searchcraft Engine 0.10.0.
     *
     * @param string $indexName The name of the index to summarize.
     * @param string|array $query The search query string or a complex query
     *                            object. Strings are wrapped in a
     *                            `fuzzy` / `exact` term per
     *                            `$options['mode']`; arrays are forwarded
     *                            to the engine verbatim.
     * @param array $options Same options as {@see self::query} plus any
     *                       additional payload fields. Unknown keys are
     *                       forwarded to the engine.
     * @param callable|null $onEvent Optional callback invoked for each SSE
     *                               event as it is received. Signature:
     *                               `fn(string $event, mixed $data): void`.
     * @return array List of parsed SSE events. Each event is shaped as
     *               `['event' => string, 'data' => mixed]`, where `data`
     *               is JSON-decoded when the SSE `data:` field parses as
     *               JSON and the raw string otherwise.
     * @throws SearchcraftException When pagination is invalid, on network
     *                              failure, or on an HTTP status >= 400.
     */
    public function searchSummary(
        string $indexName,
        $query,
        array $options = [],
        ?callable $onEvent = null
    ): array {
        $this->validatePagination($options);
        $params = $this->buildSearchParams($query, $options);

        return $this->streamRequest(
            'POST',
            "/index/{$indexName}/search/summary",
            $params,
            $onEvent
        );
    }

    /**
     * Validate pagination options (limit and offset) when supplied.
     *
     * @param array $options The caller-supplied search options array. Only
     *                       the `limit` and `offset` keys are inspected;
     *                       other keys are ignored.
     * @return void
     * @throws SearchcraftException When `limit` or `offset` is present but
     *                              fails its corresponding validator.
     */
    private function validatePagination(array $options): void
    {
        if (isset($options['limit'])) {
            Validators::validateLimit($options['limit']);
        }

        if (isset($options['offset'])) {
            Validators::validateOffset($options['offset']);
        }
    }

    /**
     * Build search parameters from query and options
     *
     * @param string|array $query The search query
     * @param array $options Additional search options
     * @return array The formatted search parameters
     * @throws SearchcraftException When a clause-level option is supplied
     *                              alongside an array query, where it cannot
     *                              be applied.
     */
    private function buildSearchParams($query, array $options = []): array
    {
        $params = [];

        // Handle query parameter
        if (is_string($query)) {
            // Get query mode - fuzzy (default), exact, or dynamic
            $mode = isset($options['mode']) && in_array($options['mode'], self::QUERY_MODES, true)
                ? $options['mode']
                : 'fuzzy';

            $clause = ['ctx' => $query];

            // `fields` belongs inside the query clause, not at the top level
            // of the request.
            if (isset($options['fields'])) {
                $clause['fields'] = $options['fields'];
            }

            $params['query'] = [
                $mode => $clause
            ];

            // `occur` sits beside the mode key inside the query object, not
            // at the top level of the request.
            if (isset($options['occur'])) {
                if (!in_array($options['occur'], self::OCCUR_MODES, true)) {
                    throw new SearchcraftException(
                        'occur must be one of: ' . implode(', ', self::OCCUR_MODES)
                    );
                }

                $params['query']['occur'] = $options['occur'];
            }
        } elseif (is_array($query)) {
            // An array query is forwarded verbatim, so clause-level options
            // have nowhere to go — they belong inside the clauses themselves.
            foreach (['fields', 'occur'] as $clauseOption) {
                if (isset($options[$clauseOption])) {
                    throw new SearchcraftException(
                        "The '{$clauseOption}' option applies to string queries only; "
                        . "set it inside the query clause when passing an array query"
                    );
                }
            }

            // Complex query object - pass as is
            $params['query'] = $query;
        }

        // Add pagination, sorting and other options
        if (isset($options['limit'])) {
            $params['limit'] = $options['limit'];
        }

        if (isset($options['offset'])) {
            $params['offset'] = $options['offset'];
        }

        if (isset($options['order_by'])) {
            $params['order_by'] = $options['order_by'];
        }

        if (isset($options['sort'])) {
            $params['sort'] = $options['sort'];
        }

        // Add any other options directly to the params
        foreach ($options as $key => $value) {
            if (!in_array($key, self::RESERVED_OPTIONS, true)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
