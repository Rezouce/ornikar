<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstructorRepository;
use App\Repository\MeetingPointRepository;
use DateTime;

class Lesson
{
    public int $id;
    public int $meetingPointId;
    public int $instructorId;
    public DateTime $startTime;
    public DateTime $endTime;

    public function __construct(
        int $id,
        int $meetingPointId,
        int $instructorId,
        DateTime $start_time,
        DateTime $end_time
    ) {
        $this->id = $id;
        $this->meetingPointId = $meetingPointId;
        $this->instructorId = $instructorId;
        $this->startTime = $start_time;
        $this->endTime = $end_time;
    }

    public function getSummary(): string
    {
        return (string)$this->id;
    }

    public function getSummaryHtml(): string
    {
        return '<p>' . $this->id . '</p>';
    }

    public function getStartDate(): string
    {
        return $this->startTime->format('d/m/Y');
    }

    public function getStartTime(): string
    {
        return $this->startTime->format('H:i');
    }

    public function getEndTime(): string
    {
        return $this->endTime->format('H:i');
    }

    public function getInstructorName(InstructorRepository $repository): string
    {
        return $repository->getById($this->instructorId)->getName();
    }

    public function getInstructorLink(InstructorRepository $repository): string
    {
        return $repository->getById($this->instructorId)->getLink();
    }

    public function getMeetingPoint(MeetingPointRepository $repository): string
    {
        return $repository->getById($this->meetingPointId)->getName();
    }
}
