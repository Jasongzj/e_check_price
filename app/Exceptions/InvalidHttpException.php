<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\JsonResponse;
use Exception;

class InvalidHttpException extends Exception
{
    use JsonResponse;
}
