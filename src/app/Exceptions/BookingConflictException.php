<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class BookingConflictException extends RuntimeException
{
    public function __construct(string $message = 'The room is already booked for the requested time slot.')
    {
        parent::__construct($message);
    }
}
