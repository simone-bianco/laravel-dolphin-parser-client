<?php

namespace SimoneBianco\DolphinParser\Exceptions;

/**
 * Exception thrown when API request fails.
 */
class ApiRequestException extends DolphinParserException
{
    public static function requestFailed(string $message, ?array $response = null): self
    {
        return new self(
            message: "API request failed: {$message}",
            response: $response
        );
    }

    public static function timeout(): self
    {
        return new self('API request timed out.');
    }

    public static function connectionError(string $message): self
    {
        return new self("Connection error: {$message}");
    }
}
