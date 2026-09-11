<?php

namespace App\Exceptions;

use RuntimeException;

/** Safe diagnostics only: no credentials or raw provider bodies. */
class PhotoScrapeException extends RuntimeException
{
    public function __construct(string $message, public bool $retryable = false)
    {
        parent::__construct($message);
    }
}
