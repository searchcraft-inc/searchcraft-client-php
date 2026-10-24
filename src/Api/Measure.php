<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * Measure API for event ingestion and dashboard reporting.
 *
 * The ingestion endpoints (`trackEvent`, `trackBatch`) are primarily used by
 * SDK and CMS integrations to capture search usage and document interaction
 * events. The dashboard endpoints (`getDashboardSummary`,
 * `getDashboardConversion`, `getDashboardUsage`) return aggregated metrics
 * suitable for rendering analytics dashboards.
 */
class Measure extends Base
{
    /**
     * Measure dashboard query filter keys accepted by the engine.
     *
     * @var string[]
     */
    private const DASHBOARD_FILTER_KEYS = [
        'organization_id',
        'application_id',
        'index_names',
        'user_id',
        'user_type',
        'session_id',
        'event_name',
        'date_start',
        'date_end',
        'granularity',
        'rpp',
        'page',
    ];

    /**
     * Track a single measure event.
     *
     * Uses POST /measure/event. The event payload should include
     * `event_name`, `properties`, and `user`. See the Searchcraft API docs
     * for the exact event shape.
     *
     * @param array $event The measure event payload. Must be a top-level
     *                     associative array with at least `event_name`,
     *                     `properties`, and `user` keys.
     * @return array Operation result from the engine, decoded from JSON.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function trackEvent(array $event): array
    {
        return $this->request('POST', '/measure/event', $event);
    }

    /**
     * Track a batch of measure events.
     *
     * Uses POST /measure/batch. Each entry should follow the same payload
     * shape as {@see trackEvent}.
     *
     * @param array $events List of measure event payloads. Each element
     *                      should be a top-level associative array shaped
     *                      like a single event passed to {@see trackEvent}.
     * @return array Operation result from the engine, decoded from JSON.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function trackBatch(array $events): array
    {
        return $this->request('POST', '/measure/batch', $events);
    }

    /**
     * Check whether Measure analytics are enabled on the server.
     *
     * Uses GET /measure/status. This endpoint is unauthenticated — it
     * works with or without an Authorization header, like
     * {@see \Searchcraft\Api\Healthcheck::check}.
     * When analytics are disabled, all other `/measure/*` endpoints are
     * no-ops, so callers can use this to decide whether issuing those
     * requests is worthwhile.
     *
     * @return array Status payload, decoded from JSON. Contains an
     *               `enabled` bool — `true` when ClickHouse-backed
     *               analytics are configured on the server, `false`
     *               otherwise.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getStatus(): array
    {
        return $this->request('GET', '/measure/status');
    }

    /**
     * Get the dashboard summary metrics.
     *
     * Uses GET /measure/dashboard/summary. Supply any of the supported
     * filters as an associative array (`organization_id`, `application_id`,
     * `index_names`, `user_id`, `user_type`, `session_id`, `event_name`,
     * `date_start`, `date_end`, `granularity`, `rpp`, `page`). Unknown keys
     * are dropped before the query string is built.
     *
     * `index_names` may be given as an array of index names; it is sent
     * pipe-delimited. `date_start` and `date_end` are Unix timestamps in
     * seconds, and `granularity` is one of `minutes`, `hours`, `days`,
     * `weeks`, `months`, or `years`.
     *
     * @param array $filters Optional associative array of query filters.
     *                       Unknown keys are dropped silently.
     * @return array Dashboard summary data, decoded from JSON.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getDashboardSummary(array $filters = []): array
    {
        return $this->request(
            'GET',
            '/measure/dashboard/summary',
            $this->filterDashboardQuery($filters)
        );
    }

    /**
     * Get the dashboard conversion metrics.
     *
     * Uses GET /measure/dashboard/conversion. Same filter surface as
     * {@see getDashboardSummary}.
     *
     * @param array $filters Optional associative array of query filters.
     *                       Unknown keys are dropped silently.
     * @return array Dashboard conversion data, decoded from JSON.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getDashboardConversion(array $filters = []): array
    {
        return $this->request(
            'GET',
            '/measure/dashboard/conversion',
            $this->filterDashboardQuery($filters)
        );
    }

    /**
     * Get the dashboard usage metrics.
     *
     * Uses GET /measure/dashboard/usage. Same filter surface as
     * {@see getDashboardSummary}.
     *
     * @param array $filters Optional associative array of query filters.
     *                       Unknown keys are dropped silently.
     * @return array Dashboard usage data, decoded from JSON.
     * @throws SearchcraftException On network failure, an invalid JSON
     *                              response, or an HTTP status >= 400.
     */
    public function getDashboardUsage(array $filters = []): array
    {
        return $this->request(
            'GET',
            '/measure/dashboard/usage',
            $this->filterDashboardQuery($filters)
        );
    }

    /**
     * Reduce a filter array to the keys the engine actually accepts and
     * normalise their wire representation.
     *
     * `index_names` is sent pipe-delimited (`a|b|c`), which is the only form
     * the engine parses. An array supplied for that key is joined here; a
     * string is passed through untouched. An empty array is dropped so the
     * filter is omitted rather than sent blank.
     *
     * @param array $filters Raw caller-supplied filter array.
     * @return array A new array containing only the keys listed in
     *               {@see self::DASHBOARD_FILTER_KEYS}, with `index_names`
     *               collapsed to a pipe-delimited string.
     */
    private function filterDashboardQuery(array $filters): array
    {
        $query = array_intersect_key(
            $filters,
            array_flip(self::DASHBOARD_FILTER_KEYS)
        );

        if (isset($query['index_names']) && is_array($query['index_names'])) {
            if (empty($query['index_names'])) {
                unset($query['index_names']);
            } else {
                $query['index_names'] = implode('|', $query['index_names']);
            }
        }

        return $query;
    }
}
