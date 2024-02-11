<?php

namespace App\DTO;

class CountryDTO
{
    /** @var ?int */
    private $id;

    /** @var ?string */
    public $name;

    /** @var ?string */
    public $iso2;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getIso2(): ?string
    {
        return $this->iso2;
    }

    public function setIso2(?string $iso2): void
    {
        $this->iso2 = $iso2;
    }
}
