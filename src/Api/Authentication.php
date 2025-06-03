<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for authentication operations with Searchcraft.
 */
class Authentication extends Base
{
    /**
     * Get all keys for an application.
     *
     * @param string $applicationId The application ID
     * @return array List of authentication keys for the application
     * @throws SearchcraftException
     */
    public function getApplicationKeys(string $applicationId): array
    {
        return $this->request('GET', "/auth/application/{$applicationId}");
    }

    /**
     * Get all keys for a federation.
     *
     * @param string $federationName The federation name
     * @return array List of authentication keys for the federation
     * @throws SearchcraftException
     */
    public function getFederationKeys(string $federationName): array
    {
        return $this->request('GET', "/auth/federation/{$federationName}");
    }

    /**
     * Get all keys for an organization.
     *
     * @param string $organizationId The organization ID
     * @return array List of authentication keys for the organization
     * @throws SearchcraftException
     */
    public function getOrganizationKeys(string $organizationId): array
    {
        return $this->request('GET', "/auth/organization/{$organizationId}");
    }

    /**
     * Get all keys on the cluster.
     *
     * @return array List of all authentication keys
     * @throws SearchcraftException
     */
    public function getAllKeys(): array
    {
        return $this->request('GET', "/auth/key");
    }

    /**
     * Create a new authentication key.
     *
     * @param array $keyConfig Key configuration
     * @return array Created key details
     * @throws SearchcraftException
     */
    public function createKey(array $keyConfig): array
    {
        return $this->request('POST', "/auth/key", $keyConfig);
    }

    /**
     * Delete all authentication keys.
     *
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteAllKeys(): array
    {
        return $this->request('DELETE', "/auth/key");
    }

    /**
     * Get a specific authentication key.
     *
     * @param string $key The authentication key
     * @return array Key details
     * @throws SearchcraftException
     */
    public function getKey(string $key): array
    {
        return $this->request('GET', "/auth/key/{$key}");
    }

    /**
     * Delete a specific authentication key.
     *
     * @param string $key The authentication key to delete
     * @return array Result of the operation
     * @throws SearchcraftException
     */
    public function deleteKey(string $key): array
    {
        if (empty($key)) {
            throw new SearchcraftException('Authentication key cannot be empty');
        }
        return $this->request('DELETE', "/auth/key/{$key}");
    }

    /**
     * Update a specific authentication key.
     *
     * @param string $key The authentication key to update
     * @param array $keyConfig Updated key configuration
     * @return array Updated key details
     * @throws SearchcraftException
     */
    public function updateKey(string $key, array $keyConfig): array
    {
        return $this->request('POST', "/auth/key/{$key}", $keyConfig);
    }
}
