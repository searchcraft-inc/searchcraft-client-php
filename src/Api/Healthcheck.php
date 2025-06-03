<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for healthcheck operations with Searchcraft.
 */
class Healthcheck extends Base
{
    /**
     * Check the health of the Searchcraft instance.
     *
     * @return array Healthcheck response
     * @throws SearchcraftException
     */
    public function check(): array
    {
        return $this->request('GET', '/healthcheck');
    }
}
