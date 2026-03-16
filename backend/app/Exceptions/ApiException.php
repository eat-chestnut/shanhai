<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $apiCode = 1,
        private readonly int $httpStatus = 400,
        private readonly array $data = [],
    ) {
        parent::__construct($message);
    }

    public function getApiCode(): int
    {
        return $this->apiCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
