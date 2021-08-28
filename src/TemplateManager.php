<?php declare(strict_types=1);

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Placeholder;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\MeetingPointRepository;
use ReflectionClass;
use ReflectionMethod;

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

                $reflection = new ReflectionMethod($object, $placeholder->getMethodName());

                foreach ($reflection->getParameters() as $parameter) {
                    if (!$this->hasDependency($parameter->getClass())) {
                        break 2;
                    }
                }

                $text = str_replace(
                    $placeholder,
                    $this->resolvePlaceholderValue($data[$placeholder->objectName], $placeholder->getMethodName()),
                    $text
                );
            }
        }

        return str_replace('[instructor_link]', $this->getInstructorLink($data), $text);
    }

    private function getInstructorLink(array $data): string
    {
        return ($data['instructor'] ?? null) instanceof Instructor
            ? 'instructors/' . $data['instructor']->id . '-' . urlencode($data['instructor']->firstname)
            : '';
    }

    private function hasDependency(ReflectionClass $reflectionClass): bool
    {
        return null !== $this->getDependency($reflectionClass);
    }

    private function getDependency(ReflectionClass $reflectionClass)
    {
        return [
            MeetingPointRepository::class => $this->meetingPointRepository,
            InstructorRepository::class => $this->instructorRepository,
        ][$reflectionClass->getName()] ?: null;
    }

    private function resolvePlaceholderValue($object, string $methodName): string
    {
        $reflection = new ReflectionMethod($object, $methodName);

        $dependencies = [];

        foreach ($reflection->getParameters() as $parameter) {
            $dependencies[] = $this->getDependency($parameter->getClass());
        }

        return $reflection->invoke($object, ...$dependencies);
    }
}
