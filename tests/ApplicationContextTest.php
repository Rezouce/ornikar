<?php declare(strict_types=1);

namespace Test;

use App\Context\ApplicationContext;
use App\Entity\Learner;
use PHPUnit\Framework\TestCase;

class ApplicationContextTest extends TestCase
{
    public function test_it_will_return_the_current_user(): void
    {
        $applicationContext = ApplicationContext::getInstance();

        $learner = new Learner(1, 'John', 'Doe', 'learner@email.com');

        $applicationContext->setCurrentUser($learner);

        self::assertSame($learner, $applicationContext->getCurrentUser());
    }
}
