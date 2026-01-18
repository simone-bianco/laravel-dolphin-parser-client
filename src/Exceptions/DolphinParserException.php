<?php

namespace SimoneBianco\DolphinParser\Exceptions;

use Exception;

/**
 * Base exception for Dolphin Parser errors.
 */
class DolphinParserException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?array $response = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the API response if available.
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }
}
