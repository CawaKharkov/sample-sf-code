<?php

namespace App\Export\Account;

use App\Entity\UserAccount;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAccountExcel
{
    /**
     * @param array $data
     * @return StreamedResponse File content
     */
    public function export(array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('AK Ecosystems')
            ->setLastModifiedBy('AK Ecosystems')
            ->setTitle('Accounts export')
            ->setSubject('Accounts')
            ->setDescription('Accounts export document, generated automatically by AK')
            ->setCategory('Accounts export');

        $fields = [
            'A' => 'Asset',
            'B' => 'Amount',
            'C' => 'Price',
            'D' => '24H Chg',
            'E' => 'Value',
        ];

        foreach ($fields as $column => $field) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue($column . '1', $field);
        }

        /** @var UserAccount $account */
        foreach ($data as $i => $account) {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('A' . ($i+2), $account->getCurrency()->getCode());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('B' . ($i+2), $account->getBalance());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('C' . ($i+2), $account->getRate());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('D' . ($i+2), $account->getChange());
            $spreadsheet->setActiveSheetIndex(0)->setCellValue('E' . ($i+2), $account->getValue());
        }

        // Save
        $writer = new Xlsx($spreadsheet);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="accounts-export.xls"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}