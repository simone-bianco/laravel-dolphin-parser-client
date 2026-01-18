<?php

namespace SimoneBianco\DolphinParser\Exceptions;

/**
 * Exception thrown when configuration is invalid.
 */
class ConfigurationException extends DolphinParserException
{
    public static function missingEndpoint(): self
    {
        return new self(
            'Dolphin Parser endpoint is not configured. Set DOLPHIN_PARSER_ENDPOINT in your .env file.'
        );
    }

    public static function missingApiKey(): self
    {
        return new self(
            'Dolphin Parser API key is not configured. Set DOLPHIN_PARSER_API_KEY in your .env file.'
        );
    }
}
