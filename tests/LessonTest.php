<?php declare(strict_types=1);

namespace Test;

use App\Entity\Instructor;
use App\Entity\Lesson;
use App\Entity\MeetingPoint;
use App\Repository\InstructorRepository;
use App\Repository\MeetingPointRepository;
use DateTime;
use PHPUnit\Framework\TestCase;

class LessonTest extends TestCase
{
    public function test_it_can_return_the_start_date(): void
    {
        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('28/08/2021', $lesson->getStartDate());
    }

    public function test_it_can_return_the_start_time(): void
    {
        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('01:02', $lesson->getStartTime());
    }

    public function test_it_can_return_the_end_time(): void
    {
        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('05:06', $lesson->getEndTime());
    }

    public function test_it_can_return_its_summary(): void
    {
        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('1', $lesson->getSummary());
    }

    public function test_it_can_return_its_summary_in_html(): void
    {
        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('<p>1</p>', $lesson->getSummaryHtml());
    }

    public function test_it_can_return_its_instructor_name(): void
    {
        $repository = InstructorRepository::getInstance();
        $repository->save(new Instructor(1, 'John', 'Doe'));

        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('John', $lesson->getInstructorName($repository));
    }

    public function test_it_can_return_its_instructor_link(): void
    {
        $repository = InstructorRepository::getInstance();
        $repository->save(new Instructor(1, 'John', 'Doe'));

        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('instructors/1-John', $lesson->getInstructorLink($repository));
    }

    public function test_it_can_return_its_meeting_point_name(): void
    {
        $repository = MeetingPointRepository::getInstance();
        $repository->save(new MeetingPoint(1, 'url', 'name'));

        $lesson = new Lesson(1, 1, 1, new DateTime('2021-08-28 01:02:03'), new DateTime('2021-08-29 05:06:07'));

        self::assertEquals('name', $lesson->getMeetingPoint($repository));
    }
}
