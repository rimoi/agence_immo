<?php

namespace App\Form\Model;
class ShowInfoFreelancer
{
    /** @var ?string */
    private $token;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
