<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstructorRepository;
use DateTime;

class Lesson
{
    public int $id;
    public int $meetingPointId;
    public int $instructorId;
    public DateTime $start_time;
    public DateTime $end_time;

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
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    public function getSummary(): string
    {
        return (string)$this->id;
    }

    public function getSummaryHtml(): string
    {
        return '<p>' . $this->id . '</p>';
    }

    public function hasMeetingPoint(): bool
    {
        return $this->meetingPointId !== 0;
    }

    public function getStartDate(): string
    {
        return $this->start_time->format('d/m/Y');
    }

    public function getStartTime(): string
    {
        return $this->start_time->format('H:i');
    }

    public function getEndTime(): string
    {
        return $this->end_time->format('H:i');
    }

    public function getInstructorName(InstructorRepository $repository): string
    {
        return $repository->getById($this->instructorId)->getName();
    }

    public function getInstructorLink(InstructorRepository $repository): string
    {
        return $repository->getById($this->instructorId)->getLink();
    }
}
