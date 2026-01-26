<?php

namespace SimoneBianco\DolphinParser\Exceptions;

use Exception;
use Throwable;

class DolphinParserException extends Exception
{
    public function __construct(
        string                 $message = '',
        int                    $code = 0,
        ?Throwable             $previous = null,
        public readonly ?array $response = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }
}
