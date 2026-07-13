<?php

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    public function render($message)
    {
        return response()->json(["error" => true, "message" => $message]);
    }
}
