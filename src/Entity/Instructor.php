<?php declare(strict_types=1);

namespace App\Entity;


class Instructor
{
    public int $id;
    public string $firstname;
    public string $lastname;

    public function __construct(int $id, string $firstname, string $lastname)
    {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function getName(): string
    {
        return $this->firstname;
    }

    public function getLink(): string
    {
        return 'instructors/' . $this->id . '-' . urlencode($this->firstname);
    }
}
