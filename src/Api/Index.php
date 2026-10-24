<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * Class for interacting with the Searchcraft index and document endpoints.
 */
class Index extends Base
{
    /**
     * List all indexes.
     *
     * @return array List of indexes
     * @throws SearchcraftException
     */
    public function listIndexes(): array
    {
        return $this->request('GET', '/index');
    }

    /**
     * Get an index details.
     *
     * @param string $indexName Index name
     * @return array Index details
     * @throws SearchcraftException
     */
    public function getIndex(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}");
    }

    /**
     * Get index statistics.
     *
     * @param string $indexName Index name
     * @return array Index stats
     * @throws SearchcraftException
     */
    public function getIndexStats(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}/stats");
    }

    /**
     * Get statistics for every index the key can reach.
     *
     * Uses GET /index/stats. The response carries `index_count`, a
     * `total_document_count` across all indexes, and an `indices` list in
     * which each entry maps an index name to its own `document_count`.
     *
     * @return array Aggregate statistics for all accessible indexes.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getStats(): array
    {
        return $this->request('GET', '/index/stats');
    }

    /**
     * Get AI capability and configuration status for an index.
     *
     * Reports whether AI features are enabled and whether an LLM provider,
     * model, and search summary configuration are set for the index.
     *
     * Added in Searchcraft Engine 0.10.0.
     *
     * @param string $indexName Index name.
     * @return array Capability details, decoded from JSON. The top-level
     *               `ai` object contains `enabled`, `searchSummaryConfigured`,
     *               `llmProviderConfigured`, and `llmModelConfigured` keys.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getCapabilities(string $indexName): array
    {
        return $this->request('GET', "/index/{$indexName}/capabilities");
    }

    /**
     * Create a new index.
     *
     * Alongside `fields`, `search_fields`, `weight_multipliers`, `language`,
     * `time_decay_field`, `auto_commit_delay`, `enable_language_stemming`,
     * `exclude_stop_words`, `ai` and `ai_enabled`, the configuration accepts
     * an `ingestion` section whose `enrichment_pipeline` names a pipeline to
     * run over documents before they are indexed.
     *
     * Each entry in `fields` takes a `type` of `text`, `facet`, `bool`,
     * `f64`, `u64`, `i64`, `datetime`, or `json`, plus the usual `indexed`,
     * `stored`, `fast`, `multi` and `required` flags. Two further options
     * are type-specific:
     *   - tokenizer: For `json` fields, how string values within the JSON
     *     are indexed — `raw` (exact, case-sensitive), `lowercase`
     *     (case-insensitive exact), or `default` (tokenized full-text).
     *     Defaults to `raw`.
     *   - expand_dots: For `json` fields, whether dots in JSON keys are
     *     treated as nested-object separators. Defaults to true.
     *
     * @param string $indexName Index name
     * @param array $options Index configuration options
     * @return array Created index details
     * @throws SearchcraftException
     */
    public function createIndex(string $indexName, array $options = []): array
    {
        $params = array_merge(['name' => $indexName], $options);
        return $this->request('POST', '/index', [ 'index' => $params ]);
    }

    /**
     * Replace an index configuration wholesale.
     *
     * Uses PUT /index/:index. Unlike {@see self::patchIndex} this is a
     * whole-schema replacement, so anything absent from `$options` reverts to
     * its default. Use it to change `fields` or to clear `search_fields`,
     * `weight_multipliers` or `language`, none of which PATCH can do. Takes
     * the same configuration surface as {@see self::createIndex}.
     *
     * @param string $indexName Index name
     * @param array $options Index configuration options to update
     * @return array Updated index details
     * @throws SearchcraftException
     */
    public function updateIndex(string $indexName, array $options): array
    {
        $params = array_merge(['name' => $indexName], $options);
        return $this->request('PUT', "/index/{$indexName}", [ 'index' => $params ]);
    }

    /**
     * Patch an index configuration.
     *
     * Allows partial configuration changes without re-ingesting data.
     * Updates are limited to search_fields, weight_multipliers, language,
     * time_decay_field, auto_commit_delay, exclude_stop_words, and top-level
     * ai / ai_enabled settings. When ai is provided the stored AI config is
     * replaced. Changing ai_enabled requires an admin-level key.
     *
     * Two limits follow from the engine's patch semantics:
     *   - `fields` is not patchable. The engine carries the existing schema
     *     fields over verbatim, so a `fields` key here is accepted and
     *     silently ignored. Use {@see self::updateIndex} to change the
     *     schema.
     *   - `search_fields`, `weight_multipliers` and `language` cannot be
     *     cleared. The engine reads an empty value as "leave unchanged", so
     *     passing `[]` or `''` is a no-op rather than a reset. Use
     *     {@see self::updateIndex} to drop them.
     *
     * Everything else follows normal patch semantics: omit a key to leave it
     * alone. `time_decay_field` can be cleared by passing an empty string.
     *
     * @param string $indexName Index name
     * @param array $options Partial configuration options to update
     * @return array Updated index details
     * @throws SearchcraftException
     */
    public function patchIndex(string $indexName, array $options): array
    {
        // Note: For PATCH operation, payload should not be nested inside an "index" object
        return $this->request('PATCH', "/index/{$indexName}", $options);
    }

    /**
     * Delete an index.
     *
     * @param string $indexName Index name
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteIndex(string $indexName): array
    {
        return $this->request('DELETE', "/index/{$indexName}");
    }
}
