<?php

namespace App\Entity\Model\Pagination;

use Knp\Component\Pager\Pagination\PaginationInterface;

class PaginationResponse
{
    /**
     * @var
     */
    private $items;

    /**
     * @var
     */
    private $total;

    /**
     * @param PaginationInterface $pagination
     */
    public function __construct(PaginationInterface $pagination)
    {
        $this->items = $pagination->getItems();
        $this->total = $pagination->getTotalItemCount();
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

}