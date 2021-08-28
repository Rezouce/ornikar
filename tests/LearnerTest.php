<?php declare(strict_types=1);

namespace Test;

use App\Entity\Learner;
use PHPUnit\Framework\TestCase;

class LearnerTest extends TestCase
{
    public function test_it_can_return_its_first_name(): void
    {
        $learner = new Learner(1, 'jOhN', 'Doe', 'learner@email.com');

        self::assertEquals('John', $learner->getFirstName());
    }
}
