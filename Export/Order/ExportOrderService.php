<?php

namespace App\Export\Order;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportOrderService
{
    public function getStreamedResponse(PaginationInterface $pagination, string $type): StreamedResponse
    {
        switch ($type) {
            case 'excel':
                $response = (new ExportOrderExcel())->export($pagination);
                break;
            case 'csv':
                throw new \Exception("CSV not implemented yet");
            default:
                throw new \Exception("Unknown export type $type");
        }

        return $response;
    }

}