<?php declare(strict_types=1);

namespace App\Helper;

trait SingletonTrait
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
