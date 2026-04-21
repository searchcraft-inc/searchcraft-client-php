<?php

declare(strict_types=1);

namespace Searchcraft\Tests\Helpers;

/**
 * Minimal in-memory StreamInterface-ish stub that yields chunks from a list.
 *
 * Used by parser tests to simulate a PSR-7 stream delivering SSE bytes in
 * arbitrary pieces. Only implements the handful of methods that
 * Searchcraft\Api\Base::parseEventStream actually calls: rewind(), eof(),
 * read().
 */
class ChunkedStreamStub
{
    /**
     * @var string[]
     */
    private $chunks;

    /**
     * @var int
     */
    private $cursor = 0;

    /**
     * @param string[] $chunks Ordered list of chunks to yield on successive
     *                         read() calls. Each entry is returned as a
     *                         single read result, regardless of length.
     */
    public function __construct(array $chunks)
    {
        $this->chunks = array_values($chunks);
    }

    /**
     * Rewind the cursor to the start of the chunk list.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->cursor = 0;
    }

    /**
     * Whether every chunk has been consumed.
     *
     * @return bool
     */
    public function eof(): bool
    {
        return $this->cursor >= count($this->chunks);
    }

    /**
     * Return the next chunk and advance the cursor.
     *
     * @param int $_length Unused; accepted for PSR-7 compatibility. Each
     *                     call returns the full next chunk.
     * @return string
     */
    public function read($_length): string
    {
        if ($this->eof()) {
            return '';
        }
        return $this->chunks[$this->cursor++];
    }
}
