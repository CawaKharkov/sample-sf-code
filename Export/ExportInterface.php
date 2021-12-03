<?php

namespace App\Export;


use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface ExportInterface
{
    /**
     * @param PaginationInterface $pagination
     * @return StreamedResponse File content
     */
    public function export(PaginationInterface $pagination) : StreamedResponse;
}