<?php declare(strict_types=1);

namespace App\Entity;

class Learner
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public string $email;

    public function __construct(int $id, string $firstName, string $lastName, string $email)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    public function getFirstName(): string
    {
        return ucfirst(strtolower($this->firstName));
    }
}
