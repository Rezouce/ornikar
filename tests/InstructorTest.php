<?php declare(strict_types=1);

namespace Test;

use App\Entity\Instructor;
use PHPUnit\Framework\TestCase;

class InstructorTest extends TestCase
{
    public function test_it_can_return_its_name(): void
    {
        $instructor = new Instructor(1, 'John', 'Doe');

        self::assertEquals('John', $instructor->getName());
    }

    public function test_it_can_return_its_link(): void
    {
        $instructor = new Instructor(1, 'John', 'Doe');

        self::assertEquals('instructors/1-John', $instructor->getLink());
    }
}
