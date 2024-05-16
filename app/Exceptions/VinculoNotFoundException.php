<?php

namespace App\Exceptions;

use Exception;

class VinculoNotFoundException extends Exception
{
    public function __construct($message = "El vínculo especificado no existe.")
    {
        parent::__construct($message);
    }

    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], 422);
    }
}
