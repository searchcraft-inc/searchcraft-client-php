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
     * Get the dashboard summary metrics.
     *
     * Uses GET /measure/dashboard/summary. Supply any of the supported
     * filters as an associative array (`organization_id`, `application_id`,
     * `index_names`, `user_id`, `user_type`, `session_id`, `event_name`,
     * `date_start`, `date_end`, `granularity`, `rpp`, `page`). Unknown keys
     * are dropped before the query string is built.
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
     * Reduce a filter array to the keys the engine actually accepts.
     *
     * @param array $filters Raw caller-supplied filter array.
     * @return array A new array containing only the keys listed in
     *               {@see self::DASHBOARD_FILTER_KEYS}.
     */
    private function filterDashboardQuery(array $filters): array
    {
        return array_intersect_key(
            $filters,
            array_flip(self::DASHBOARD_FILTER_KEYS)
        );
    }
}
