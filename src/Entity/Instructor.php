<?php declare(strict_types=1);

namespace App\Entity;


class Instructor
{
    public int $id;
    public string $firstName;
    public string $lastName;

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getName(): string
    {
        return $this->firstName;
    }

    public function getLink(): string
    {
        return 'instructors/' . $this->id . '-' . urlencode($this->firstName);
    }
}
