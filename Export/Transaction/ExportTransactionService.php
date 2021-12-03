<?php

namespace App\Export\Transaction;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTransactionService
{
    public function getStreamedResponse(PaginationInterface $pagination, string $type): StreamedResponse
    {
        switch ($type) {
            case 'excel':
                $response = (new ExportTransactionExcel())->export($pagination);
                break;
            case 'csv':
                throw new \Exception("CSV not implemented yet");
            default:
                throw new \Exception("Unknown export type $type");
        }

        return $response;
    }

}