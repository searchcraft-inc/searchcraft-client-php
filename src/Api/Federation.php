<?php

declare(strict_types=1);

namespace Searchcraft\Api;

use Searchcraft\Exception\SearchcraftException;

/**
 * API client for federation management operations with Searchcraft.
 */
class Federation extends Base
{
    /**
     * List all federations.
     *
     * @return array List of federations
     * @throws SearchcraftException
     */
    public function listFederations(): array
    {
        return $this->request('GET', '/federation');
    }

    /**
     * Get details of a specific federation.
     *
     * @param string $federationName The name of the federation
     * @return array Federation details
     * @throws SearchcraftException
     */
    public function getFederation(string $federationName): array
    {
        return $this->request('GET', "/federation/{$federationName}");
    }

    /**
     * List all federations for an organization.
     *
     * @param string $organizationId The organization ID
     * @return array List of federations for the organization
     * @throws SearchcraftException
     */
    public function getFederationsByOrganization(string $organizationId): array
    {
        return $this->request('GET', "/federation/organization/{$organizationId}");
    }

    /**
     * Create a new federation.
     *
     * @param array $federation Federation configuration
     * @return array Created federation details
     * @throws SearchcraftException
     */
    public function createFederation(array $federation): array
    {
        return $this->request('POST', '/federation', $federation);
    }

    /**
     * Update an existing federation.
     *
     * @param string $federationName The name of the federation to update
     * @param array $federation Updated federation configuration
     * @return array Updated federation details
     * @throws SearchcraftException
     */
    public function updateFederation(string $federationName, array $federation): array
    {
        return $this->request('PUT', "/federation/{$federationName}", $federation);
    }

    /**
     * Delete a federation.
     *
     * @param string $federationName The name of the federation to delete
     * @return array Deletion result
     * @throws SearchcraftException
     */
    public function deleteFederation(string $federationName): array
    {
        return $this->request('DELETE', "/federation/{$federationName}");
    }
}
