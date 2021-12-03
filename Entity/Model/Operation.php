<?php

namespace App\Entity\Model;

use JMS\Serializer\Annotation as JMS;


/**
 * @JMS\ExclusionPolicy("all")
 */
class Operation
{
    /**
     * @var string
     *
     * @JMS\Expose
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     *
     * @JMS\Expose
     */
    private $createdAt;

    /**
     * @var int
     *
     * @JMS\Expose
     */
    private $type;

    /**
     * @var string
     *
     * @JMS\Expose
     */
    private $currencyCode;

    /**
     * @var int
     *
     * @JMS\Expose
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * @return string
     *
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

}