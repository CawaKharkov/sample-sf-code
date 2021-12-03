<?php

namespace App\Export\Account;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAccountService
{
    public function getStreamedResponse(array $accounts, string $type): StreamedResponse
    {
        switch ($type) {
            case 'excel':
                $response = (new ExportAccountExcel())->export($accounts);
                break;
            case 'csv':
                throw new \Exception("CSV not implemented yet");
            default:
                throw new \Exception("Unknown export type $type");
        }

        return $response;
    }

}