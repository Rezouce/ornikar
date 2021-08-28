<?php declare(strict_types=1);

namespace App\Context;

use App\Entity\Learner;
use App\Helper\SingletonTrait;

class ApplicationContext
{
    use SingletonTrait;

    private Learner $currentUser;

    private function __construct()
    {
    }

    public function getCurrentUser(): Learner
    {
        return $this->currentUser;
    }

    public function setCurrentUser(Learner $currentUser): void
    {
        $this->currentUser = $currentUser;
    }
}
