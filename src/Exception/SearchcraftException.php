<?php

declare(strict_types=1);

namespace Searchcraft\Exception;

/**
 * Exception thrown when an error occurs in the Searchcraft API client.
 */
class SearchcraftException extends \Exception
{
    /**
     * @var array|null
     */
    private $errorData;

    /**
     * @param string $message The error message
     * @param int $code The error code
     * @param \Throwable|null $previous Previous exception
     * @param array|null $errorData Additional error data from the API
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        ?array $errorData = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorData = $errorData;
    }

    /**
     * Get additional error data from the API.
     *
     * @return array|null
     */
    public function getErrorData(): ?array
    {
        return $this->errorData;
    }

    /**
    * Create an exception from an API error response
    *
    * @param array $responseData The parsed API response
    * @param int $statusCode The HTTP status code
    * @return self
    */
    public static function fromApiResponse(array $responseData, int $statusCode): self
    {
        // Standard error format with 'error' object
        if (isset($responseData['error'])) {
            if (is_array($responseData['error'])) {
                $message = $responseData['error']['message'] ?? 'Unknown API error';
                $code = $responseData['error']['code'] ?? $statusCode;
                return new self($message, $code, null, $responseData['error']);
            } else {
                return new self('Unknown API error', $statusCode, null, ['__original_error' => $responseData['error']]);
            }
        }

        // Handle API format: { "status": 400, "data": "error message" }
        if (isset($responseData['status']) && isset($responseData['data']) && is_string($responseData['data'])) {
            // Convert to consistent error format
            $errorData = [
                'message' => $responseData['data'],
                'code' => $responseData['status']
            ];
            return new self($responseData['data'], (int)$responseData['status'], null, $errorData);
        }

        // Handle API format: { "status": 400, "data": { "error": "...", "details": "..." } }
        if (isset($responseData['status']) && isset($responseData['data']) && is_array($responseData['data'])) {
            $data = $responseData['data'];
            $message = $data['error'] ?? $data['message'] ?? 'Unknown API error';
            $code = (int)$responseData['status'];

            // Include details if available
            if (isset($data['details'])) {
                $message .= ' - ' . $data['details'];
            }

            return new self($message, $code, null, $data);
        }

        // Fallback for any other error format
        return new self('Unknown API error', $statusCode);
    }
}
