<?php

namespace App\Export\Order;

use App\Entity\Order;
use App\Export\ExportInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportOrderExcel implements ExportInterface
{
    /**
     * @param PaginationInterface $pagination
     * @return StreamedResponse File content
     */
    public function export(PaginationInterface $pagination): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('AK Ecosystems')
            ->setLastModifiedBy('AK Ecosystems')
            ->setTitle('Orders export')
            ->setSubject('Orders')
            ->setDescription('Orders export document, generated automatically by AK')
            ->setCategory('Orders export');

        $fields = [
            'A' => 'Type',
            'B' => 'Status',
            'C' => 'Amount',
            'D' => 'Direction',
            'E' => 'Price',
            'F' => 'CreatedAt'
        ];

        $items = $pagination->getItems();

        foreach ($fields as $column => $field) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue($column . '1', $field);
        }

        /** @var Order $order */
        foreach ($items as $i => $order) {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . ($i+2), $order->getTypeText());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B' . ($i+2), $order->getStatusText());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C' . ($i+2), $order->getAmount());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D' . ($i+2), $order->getDirection()->getCode());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E' . ($i+2), $order->getPriceOrFilled());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('F' . ($i+2), $order->getCreatedAt());
        }

        // Save
        $writer = new Xlsx($spreadsheet);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="orders-export.xls"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}