<?php

namespace App\Exceptions;

/** An operational RAG error whose message is safe to show to users. */
class RagException extends \RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly bool $retryable = true,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function permanent(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, 0, $previous, false);
    }
}
