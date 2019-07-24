<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\JsonResponse;
use Exception;

class BarcodeApiException extends Exception
{
    use JsonResponse;

    public function render()
    {
        return $this->failed($this->getMessage(), 4003);
    }
}
