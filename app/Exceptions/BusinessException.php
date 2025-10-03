<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $data;

    public function __construct(string $message = "", int $code = 400, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
