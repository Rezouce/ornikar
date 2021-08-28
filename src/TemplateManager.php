<?php declare(strict_types=1);

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Lesson;
use App\Entity\Placeholder;
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

    /**
     * @return Placeholder[]
     */
    private function findPlaceholders(string $text): array
    {
        $placeholders = [];

        preg_match_all('/\[(?:([a-z]+):)?([a-z_]+)]/', $text, $matches);

        foreach ($matches[0] as $key => $match) {
            $placeholders[] = new Placeholder($matches[1][$key], $matches[2][$key]);
        }

        return $placeholders;
    }

    private function computeText($text, array $data): string
    {
        if (!isset($data['user'])) {
            $data['user'] = $this->applicationContext->getCurrentUser();
        }

        foreach ($this->findPlaceholders($text) as $placeholder) {
            $object = $data[$placeholder->objectName] ?? null;

            if ($object && method_exists($object, $placeholder->getMethodName())) {
                $text = str_replace(
                    $placeholder,
                    $data[$placeholder->objectName]->{$placeholder->getMethodName()}(),
                    $text
                );
            }
        }

        $lesson = $data['lesson'] ?: null;

        if ($lesson instanceof Lesson) {
            $text = $this->computeLesson($text, $lesson);
        }

        if ($lesson->hasMeetingPoint() && strpos($text, '[lesson:meeting_point]') !== false) {
            $meetingPoint = $this->meetingPointRepository->getById($lesson->meetingPointId);

            $text = str_replace('[lesson:meeting_point]', $meetingPoint->name, $text);
        }

        return str_replace('[instructor_link]', $this->getInstructorLink($data), $text);
    }

    private function computeLesson($text, Lesson $lesson): string
    {
        $instructor = $this->instructorRepository->getById($lesson->instructorId);

        return str_replace([
            '[lesson:instructor_link]',
            '[lesson:instructor_name]',
        ], [
            $this->getInstructorLink(['instructor' => $instructor]),
            $this->getInstructorFirstName(['instructor' => $instructor]),
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
}
