# Changelog

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
