<?php

namespace App\Entity\Model;

use App\Entity\OrderFillingOperation;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Envelop for OrderFillingOperations per one deal (order filling)
 */
class OrderFilling
{
    /**
     * @var OrderFillingOperation
     */
    private $taker;

    /**
     * @var OrderFillingOperation[]|ArrayCollection
     */
    private $makers;

    public function __construct()
    {
        return $this;
    }

    /**
     * @return OrderFillingOperation
     */
    public function getTaker()
    {
        return $this->taker;
    }

    /**
     * @param OrderFillingOperation $taker
     * @return OrderFilling
     */
    public function setTaker(OrderFillingOperation $taker): self
    {
        $taker->setSide(OrderFillingOperation::SIDE_TAKER);

        $this->taker = $taker;

        return $this;
    }

    /**
     * @return OrderFillingOperation[]|ArrayCollection
     */
    public function getMakers()
    {
        return $this->makers;
    }

    /**
     * @param iterable $makers
     * @return OrderFilling
     */
    public function setMakers(iterable $makers): self
    {
        /** @var OrderFillingOperation $maker */
        foreach ($makers as $maker) {
            if (! $maker instanceof OrderFillingOperation) {
                throw new \InvalidArgumentException('Maker type error: got unknown class' . get_class($maker));
            }
            $maker->setSide(OrderFillingOperation::SIDE_MAKER);
        }
        $this->makers = $makers;

        return $this;
    }



}