<?php

declare(strict_types=1);

namespace Searchcraft;

/**
 * Permission bits an authentication key can carry.
 *
 * The engine stores key permissions as a bitmask rather than a fixed set of
 * levels, and the set of bits grows over time. Combine flags with the bitwise
 * OR operator, and pass the result to
 * {@see \Searchcraft\Api\Authentication::checkKeyPermissions()} or to the
 * `permissions` field of a key configuration.
 */
final class Permissions
{
    /** Search the key's permitted indexes. */
    public const SEARCH = 1 << 0;

    /** Add and remove synonyms. */
    public const MODIFY_SYNONYMS = 1 << 1;

    /** Add and remove stop words. */
    public const MODIFY_STOP_WORDS = 1 << 2;

    /** Add and remove documents. */
    public const MODIFY_DOCUMENTS = 1 << 3;

    /** Create and delete indexes. */
    public const MODIFY_INDEX = 1 << 4;

    /** Create, revoke and modify auth keys. */
    public const MODIFY_AUTH = 1 << 5;

    /** Generate AI search summaries. */
    public const LLM_RAG_SUMMARIES = 1 << 6;

    /** Perform LLM-expanded keyword searches. */
    public const LLM_EXPANDED_KEYWORD_SEARCH = 1 << 7;

    /** Read analytics from the `/measure/dashboard/*` endpoints. */
    public const READ_ANALYTICS = 1 << 8;

    /** Search only — the conventional read key (1). */
    public const PRESET_READ = self::SEARCH;

    /** Search plus document, synonym and stop word writes (15). */
    public const PRESET_INGEST = self::SEARCH
        | self::MODIFY_SYNONYMS
        | self::MODIFY_STOP_WORDS
        | self::MODIFY_DOCUMENTS;

    /** Ingest plus index and auth management (63). */
    public const PRESET_ADMIN = self::PRESET_INGEST
        | self::MODIFY_INDEX
        | self::MODIFY_AUTH;

    /**
     * Every permission the engine defines, including the AI and analytics
     * bits (511). This exceeds 255 — a key's permission is a number, never
     * one of a fixed set of levels.
     */
    public const PRESET_SUPER_USER = self::PRESET_ADMIN
        | self::LLM_RAG_SUMMARIES
        | self::LLM_EXPANDED_KEYWORD_SEARCH
        | self::READ_ANALYTICS;

    /**
     * Add-on: AI summaries and semantic search (64). Combine with a level,
     * e.g. `PRESET_READ | PRESET_AI` for a public key that can request AI
     * summaries.
     */
    public const PRESET_AI = self::LLM_RAG_SUMMARIES;

    /** Add-on: read the measure dashboards (256). */
    public const PRESET_ANALYTICS = self::READ_ANALYTICS;
}
