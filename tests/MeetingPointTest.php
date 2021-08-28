<?php declare(strict_types=1);

namespace Test;

use App\Entity\MeetingPoint;
use PHPUnit\Framework\TestCase;

class MeetingPointTest extends TestCase
{
    public function test_it_can_return_its_name(): void
    {
        $meetingPoint = new MeetingPoint(1, 'url', 'name');

        self::assertEquals('name', $meetingPoint->getName());
    }
}
