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
    private MeetingPointRepository $meetingPointRepository;
    private InstructorRepository $instructorRepository;

    public function __construct(
        ApplicationContext $applicationContext,
        MeetingPointRepository $meetingPointRepository,
        InstructorRepository $instructorRepository
    ) {
        $this->applicationContext = $applicationContext;
        $this->meetingPointRepository = $meetingPointRepository;
        $this->instructorRepository = $instructorRepository;
    }

    public function getTemplateComputed(Template $template, array $data): Template
    {
        $computedTemplate = clone $template;
        $computedTemplate->subject = $this->computeText($computedTemplate->subject, $data);
        $computedTemplate->content = $this->computeText($computedTemplate->content, $data);

        return $computedTemplate;
    }

    private function computeText($text, array $data): string
    {
        $lesson = $data['lesson'] ?: null;

        if ($lesson instanceof Lesson) {
            $text = $this->computeLesson($text, $lesson);
        }

        if ($lesson->hasMeetingPoint() && strpos($text, '[lesson:meeting_point]') !== false) {
            $meetingPoint = $this->meetingPointRepository->getById($lesson->meetingPointId);

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
        $instructor = $this->instructorRepository->getById($lesson->instructorId);

        return str_replace([
            '[lesson:instructor_link]',
            '[lesson:instructor_name]',
            '[lesson:summary_html]',
            '[lesson:summary]',
        ], [
            $this->getInstructorLink(['instructor' => $instructor]),
            $this->getInstructorFirstName(['instructor' => $instructor]),
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

    private function getInstructorFirstName(array $data): string
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
