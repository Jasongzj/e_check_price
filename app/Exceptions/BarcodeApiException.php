<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\JsonResponse;
use Exception;

class BarcodeApiException extends Exception
{
    use JsonResponse;

    public function render()
    {
        return $this->notFound($this->getMessage(), 40005);
    }
}
