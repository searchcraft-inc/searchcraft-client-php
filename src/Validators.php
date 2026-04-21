<?php

declare(strict_types=1);

namespace Searchcraft;

use Searchcraft\Exception\SearchcraftException;

/**
 * Lightweight input validators for search and measure request parameters.
 *
 * These helpers produce fast, client-side failures so callers see typed
 * errors before a network round-trip. They mirror the validation surface of
 * the official TypeScript client so the two stay in lock-step.
 */
final class Validators
{
    /**
     * Maximum search `limit` allowed by the engine.
     *
     * Requests above this value are rejected client-side to avoid a wasted
     * round-trip; the engine would otherwise cap the result set to this
     * number per its `max_result_limit` configuration.
     *
     * @var int
     */
    public const MAX_SEARCH_LIMIT = 200;

    /**
     * Ensure a search `limit` is a positive integer no greater than 200.
     *
     * @param mixed $limit The limit value to validate. Only positive `int`
     *                     values up to {@see self::MAX_SEARCH_LIMIT} are
     *                     accepted; anything else raises a
     *                     {@see SearchcraftException}.
     *
     * @return void
     * @throws SearchcraftException When the value is not a positive integer
     *                              or exceeds the engine-side cap.
     */
    public static function validateLimit($limit): void
    {
        if (!is_int($limit) || $limit <= 0) {
            throw new SearchcraftException('limit must be a positive integer');
        }

        if ($limit > self::MAX_SEARCH_LIMIT) {
            throw new SearchcraftException(
                'limit cannot exceed ' . self::MAX_SEARCH_LIMIT
            );
        }
    }

    /**
     * Ensure a search `offset` is a non-negative integer.
     *
     * @param mixed $offset The offset value to validate. Must be an `int`
     *                      of zero or greater; any other value raises a
     *                      {@see SearchcraftException}.
     *
     * @return void
     * @throws SearchcraftException When the value is not a non-negative
     *                              integer.
     */
    public static function validateOffset($offset): void
    {
        if (!is_int($offset) || $offset < 0) {
            throw new SearchcraftException('offset must be a non-negative integer');
        }
    }
}
