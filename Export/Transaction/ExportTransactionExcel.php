<?php

namespace App\Export\Transaction;

use App\Entity\Transaction;
use App\Export\ExportInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTransactionExcel implements ExportInterface
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
            ->setTitle('Transactions export')
            ->setSubject('Transactions')
            ->setDescription('Transactions export document, generated automatically by AK')
            ->setCategory('Transactions export');

        $fields = [
            'A' => 'Date',
            'B' => 'Asset',
            'C' => 'Type',
            'D' => 'Amount',
            'E' => 'Description',
        ];

        $items = $pagination->getItems();

        foreach ($fields as $column => $field) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue($column . '1', $field);
        }

        /** @var Transaction $transaction */
        foreach ($items as $i => $transaction) {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . ($i+2), $transaction->getCreatedAt());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B' . ($i+2), $transaction->getAsset());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C' . ($i+2), $transaction->getTypeText());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D' . ($i+2), $transaction->getAmount());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E' . ($i+2), $transaction->getDescription());
        }

        // Save
        $writer = new Xlsx($spreadsheet);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="transactions-export.xls"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}