# Changelog

## 0.8.2

Support for Searchcraft Engine 0.11.0, 0.11.1.

### Behavior changes

- **API error messages no longer carry the `"API request failed: "` prefix.**
  Errors raised from an HTTP status now surface the engine's own message, with
  `getCode()` returning the status and `getErrorData()` the decoded payload.
  Transport failures — a network error or a client exception — keep the old
  prefixed message and a code of `0`. Code that matched on the prefix to
  detect API errors should switch to `getCode()`.
- **`occur` and `fields` are rejected when passed alongside an array query.**
  Both describe a single clause and have nowhere to go once the query is
  forwarded verbatim, so supplying either now raises a `SearchcraftException`
  rather than being silently dropped or sent at the top level, where the
  engine ignored it.

### Fixes

- **Measure dashboard `index_names` filters were ignored.** The engine parses
  that filter pipe-delimited (`a|b|c`); the client passed the array straight to
  `http_build_query`, producing `index_names[0]=a&index_names[1]=b`, which the
  server dropped. An array is now joined before the query string is built, and
  an empty array omits the filter entirely.
- **API errors lost their status code and error data.** `Base::request()`
  caught every `Throwable` — including the typed `SearchcraftException` that
  `handleResponse()` had just raised — and re-wrapped it as
  `"API request failed: ..."` with code `0`. Typed API errors are now
  re-thrown verbatim, so `getCode()` and `getErrorData()` survive.
- **An error response without a JSON body reported a decoding error rather
  than its status.** `handleResponse()` required the body to parse as JSON
  before it looked at the status, so a 403 or 500 carrying an empty or
  plain-text body raised `"Invalid JSON response from API"` with code `0`.
  The status is now resolved first, and a non-JSON body becomes the error
  message — matching how `streamRequest()` already handled it. A successful
  response whose JSON is valid but not an object is still reported as an
  invalid response, instead of surfacing as a `TypeError` wrapped in a
  transport error.
- **`occur` never reached the engine.** It was documented as a search option
  but forwarded to the top level of the request, where the engine ignores it;
  the query object is where it belongs, beside the mode key. It now applies
  correctly to string queries and is validated against `should`, `must`, and
  `mustnot`.
- **`mode => 'dynamic'` silently searched fuzzy.** `Search::query()` tested
  only for `'exact'` and fell back to `'fuzzy'` for everything else, leaving
  the `dynamic` query kind unreachable from the string-query path.
- Doc: the `createIndex()` and `updateIndex()` README examples wrapped their
  configuration in an extra `'index' => [...]` key, which the client adds
  itself — following them produced a doubly-nested payload.

### Added

- `Index::getStats()` wraps `GET /index/stats`, returning `index_count`,
  `total_document_count`, and per-index document counts across every index the
  key can reach. `Index::getIndexStats()` remains the single-index call.
- `Federation::listFederationIndexes()` wraps
  `GET /federation/:name/indices`.
- `Federation::getFederationStats()` wraps `GET /federation/:name/stats`.
- `Stopwords::getDefaultStopwords()` wraps
  `GET /index/:index/stopwords/default`, returning the engine's built-in
  stopword list for the index language.
- `Authentication::checkKeyPermissions()` wraps
  `GET /auth/key/check/:permission`, reporting a 403 as `false` rather than
  raising.
- `Searchcraft\Permissions` with the engine's permission bits (`SEARCH`,
  `MODIFY_SYNONYMS`, `MODIFY_STOP_WORDS`, `MODIFY_DOCUMENTS`, `MODIFY_INDEX`,
  `MODIFY_AUTH`, `LLM_RAG_SUMMARIES`, `LLM_EXPANDED_KEYWORD_SEARCH`,
  `READ_ANALYTICS`) and the conventional presets `PRESET_READ`,
  `PRESET_INGEST`, `PRESET_ADMIN`, and `PRESET_SUPER_USER`.
- `fields` search option, nested into the query clause rather than sent at the
  top level. Accepts a field name, a list of names, or a map of name to boost,
  and scopes a `fuzzy` or `term` clause to those fields instead of the index's
  configured `search_fields`.
- `'dynamic'` as a `mode` for string queries, alongside `fuzzy` and `exact`.
- `Searchcraft\Permissions` presets and bits are covered by tests asserting
  they match the engine values the TypeScript client uses.

### Documented

These already reached the engine through the client's pass-through of unknown
keys, but nothing said so:

- Search options `minimum_number_should_match` (new in engine 0.11.0),
  `time_decay_field`, and `index_weighting`.
- The `term` and `more-like-this` query kinds, and `'mustnot'` as an `occur`
  mode, all reachable by passing an array query.
- `ingestion.enrichment_pipeline` on an index config, and the `json` field
  type with its `tokenizer` and `expand_dots` options.
- `patchIndex()` cannot change `fields`, nor clear `search_fields`,
  `weight_multipliers` or `language` — the engine reads an empty value as
  "leave unchanged". `updateIndex()` is a whole-schema replacement and is the
  way to do either.
- Search responses report `time_taken` in seconds, and an `order_by` query
  returns hits with a null `score`.
- Measure dashboard `date_start` / `date_end` are Unix timestamps in seconds,
  and `granularity` is one of `minutes`, `hours`, `days`, `weeks`, `months`,
  `years`.

## 0.8.1

- New: `Measure::getStatus()` wraps the unauthenticated `GET /measure/status`
  and returns the server's analytics status.

## 0.8.0

Support for Searchcraft Engine 0.10.0.

- New: `Index::getCapabilities()` wraps `GET /index/:index/capabilities` and
  returns the index's AI capability flags (`enabled`,
  `searchSummaryConfigured`, `llmProviderConfigured`, `llmModelConfigured`).
- New: `Search::searchSummary()` wraps `POST /index/:index/search/summary`,
  parses the Server-Sent Events stream, and returns the tagged `metadata`,
  `delta`, `done`, and `error` events. A callback may be supplied to react
  to events as they arrive.
- New: `Authentication::getIndexKeys()` wraps `GET /auth/index/:index_name`.
- New: `Measure` API — wrapper for analytic events
  (`trackEvent`, `trackBatch`) and dashboard reporting
  (`getDashboardSummary`, `getDashboardConversion`, `getDashboardUsage`).
- New: `Searchcraft\Validators` helper with `validateLimit` and
  `validateOffset`. `Search::query`, `Search::federatedQuery`, and
  `Search::searchSummary` now fail fast on invalid pagination before
  issuing a request.
- Doc: `Index::patchIndex()` docblock now notes that PATCH supports
  top-level `ai` and `ai_enabled` updates (changing `ai_enabled` requires
  an admin-level key).
- Fix: `Base::request()` previously appended the query string to a local
  variable when building GET requests but never rebuilt the PSR-7
  request, so GET query parameters were silently dropped. The request is
  now created once with the fully-qualified URL.

## 0.7.5

Initial public release of the Searchcraft PHP client. Supports Searchcraft
Engine 0.9.x with the following API surface:

- `Search` — `query()`, `federatedQuery()`.
- `Index` — `listIndexes()`, `getIndex()`, `getIndexStats()`,
  `createIndex()`, `updateIndex()`, `patchIndex()`, `deleteIndex()`,
  plus document-management helpers.
- `Documents` — bulk add/update/get/delete operations.
- `Federation` — list/get/create/update/delete.
- `Authentication` — per-application, per-federation, per-organization,
  and per-key management.
- `Healthcheck`, `Stopwords`, `Synonyms`, `Transactions`.
- PSR-18 HTTP client / PSR-17 factory discovery with optional injection.
