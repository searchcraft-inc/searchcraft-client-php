<?php

use Searchcraft\Permissions;

test('Permissions bits are distinct powers of two', function () {
    $bits = [
        Permissions::SEARCH,
        Permissions::MODIFY_SYNONYMS,
        Permissions::MODIFY_STOP_WORDS,
        Permissions::MODIFY_DOCUMENTS,
        Permissions::MODIFY_INDEX,
        Permissions::MODIFY_AUTH,
        Permissions::LLM_RAG_SUMMARIES,
        Permissions::LLM_EXPANDED_KEYWORD_SEARCH,
        Permissions::READ_ANALYTICS,
    ];

    expect($bits)->toBe([1, 2, 4, 8, 16, 32, 64, 128, 256]);
    expect(array_unique($bits))->toHaveCount(count($bits));
});

test('Permissions presets match the engine values', function () {
    // These mirror the official TypeScript client so the two stay in
    // lock-step. SUPER_USER exceeds 255 because it carries the AI and
    // analytics bits.
    expect(Permissions::PRESET_READ)->toBe(1);
    expect(Permissions::PRESET_INGEST)->toBe(15);
    expect(Permissions::PRESET_ADMIN)->toBe(63);
    expect(Permissions::PRESET_SUPER_USER)->toBe(511);
});

test('Permissions presets are composed of the expected bits', function () {
    expect(Permissions::PRESET_INGEST & Permissions::MODIFY_DOCUMENTS)
        ->toBe(Permissions::MODIFY_DOCUMENTS);

    // An ingest key cannot manage indexes or auth keys.
    expect(Permissions::PRESET_INGEST & Permissions::MODIFY_INDEX)->toBe(0);
    expect(Permissions::PRESET_INGEST & Permissions::MODIFY_AUTH)->toBe(0);

    // An admin key carries everything an ingest key does, but no AI or
    // analytics bits.
    expect(Permissions::PRESET_ADMIN & Permissions::PRESET_INGEST)
        ->toBe(Permissions::PRESET_INGEST);
    expect(Permissions::PRESET_ADMIN & Permissions::READ_ANALYTICS)->toBe(0);

    // A super user carries everything an admin does, plus the rest.
    expect(Permissions::PRESET_SUPER_USER & Permissions::PRESET_ADMIN)
        ->toBe(Permissions::PRESET_ADMIN);
    expect(Permissions::PRESET_SUPER_USER & Permissions::READ_ANALYTICS)
        ->toBe(Permissions::READ_ANALYTICS);
});

test('Permissions levels combine with add-ons', function () {
    expect(Permissions::PRESET_AI)->toBe(Permissions::LLM_RAG_SUMMARIES);
    expect(Permissions::PRESET_ANALYTICS)->toBe(Permissions::READ_ANALYTICS);

    expect(Permissions::PRESET_READ | Permissions::PRESET_AI)->toBe(65);
    expect(Permissions::PRESET_READ | Permissions::PRESET_AI | Permissions::PRESET_ANALYTICS)
        ->toBe(321);

    // Every add-on on top of admin still stops short of a super user.
    expect(Permissions::PRESET_ADMIN | Permissions::PRESET_AI | Permissions::PRESET_ANALYTICS)
        ->toBe(383);
    expect(Permissions::PRESET_SUPER_USER)->toBe(511);
});
