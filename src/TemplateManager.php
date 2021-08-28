<?php declare(strict_types=1);

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\MeetingPointRepository;

class TemplateManager
{
    private ApplicationContext $applicationContext;

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    public function getTemplateComputed(Template $tpl, array $data): Template
    {
        $replaced = clone $tpl;
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data): string
    {
        $lesson = $data['lesson'] ?: null;

        if ($lesson instanceof Lesson) {
            $text = $this->computeLesson($text, $lesson);
        }

        if ($lesson->hasMeetingPoint() && strpos($text, '[lesson:meeting_point]') !== false) {
            $meetingPoint = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);

            $text = str_replace('[lesson:meeting_point]', $meetingPoint->name, $text);
        }

        return str_replace([
            '[lesson:start_date]',
            '[lesson:start_time]',
            '[lesson:end_time]',
            '[instructor_link]',
            '[user:first_name]',
        ], [
            $lesson->start_time->format('d/m/Y'),
            $lesson->start_time->format('H:i'),
            $lesson->end_time->format('H:i'),
            $this->getInstructorLink($data),
            $this->getUserFirstName($data)
        ], $text);
    }

    private function computeLesson($text, Lesson $lesson): string
    {
        $instructorOfLesson = InstructorRepository::getInstance()->getById($lesson->instructorId);

        return str_replace([
            '[lesson:instructor_link]',
            '[lesson:instructor_name]',
            '[lesson:summary_html]',
            '[lesson:summary]',
        ], [
            $this->getInstructorLink(['instructor' => $instructorOfLesson]),
            $this->getInstructorName(['instructor' => $instructorOfLesson]),
            Lesson::renderHtml($lesson),
            Lesson::renderText($lesson),
        ], $text);
    }

    private function getInstructorLink(array $data): string
    {
        return ($data['instructor'] ?? null) instanceof Instructor
            ? 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname)
            : '';
    }

    private function getInstructorName(array $data): string
    {
        return ($data['instructor'] ?? null) instanceof Instructor
            ? $data['instructor']->firstname
            : '';
    }

    private function getUserFirstName(array $data): string
    {
        $user = ($data['user'] ?? null) instanceof Learner
            ? $data['user']
            : $this->applicationContext->getCurrentUser();

        return ucfirst(strtolower($user->firstname));
    }
}
