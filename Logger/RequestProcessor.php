<?php

namespace App\Logger;

class RequestProcessor
{
    private $process;

    public function __construct()
    {
        if (is_null($this->process)) {
            $this->process = uniqid('process_', true);
        }
    }

    public function __invoke(array $record)
    {
        $record['extra']['token'] = $this->process;
        return $record;
    }
}