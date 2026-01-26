<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'Insufficient wallet balance';
}
