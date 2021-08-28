<?php declare(strict_types=1);

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Placeholder;
use App\Entity\Template;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

/**
 * This class allow to compute a template. It will replace each found placeholder by its matching value
 * in the provided data.
 *
 * For example, if your template contains a [lesson:instructor_name], the TemplateManager object will
 * look for a key named 'lesson' in the provided array $data. When found, it will call on it the getter
 * 'getInstructorName()'.
 *
 * If a getter has a dependency (eg. the Lesson::getInstructorName() requires a IntructorRepository object),
 * you need to inject this dependency to the TemplateManager using the addDependency method.
 *
 * If a template has a [user:xxx] placeholder but no user data is provided, the TemplateManager will
 * assume we're using the current user (provided by the ApplicationContext object).
 */
class TemplateManager
{
    private ApplicationContext $applicationContext;
    private array $dependencies = [];

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    public function addDependency($dependency): self
    {
        $this->dependencies[get_class($dependency)] = $dependency;

        return $this;
    }

    public function getTemplateComputed(Template $template, array $data): Template
    {
        if (!isset($data['user'])) {
            $data['user'] = $this->applicationContext->getCurrentUser();
        }

        $computedTemplate = clone $template;
        $computedTemplate->subject = $this->computeText($computedTemplate->subject, $data);
        $computedTemplate->content = $this->computeText($computedTemplate->content, $data);

        return $computedTemplate;
    }

    private function computeText($text, array $data): string
    {
        foreach ($this->findPlaceholders($text) as $placeholder) {
            $text = $this->replacePlaceholderByItsValue($placeholder, $text, $data);
        }

        return $text;
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

    private function replacePlaceholderByItsValue(Placeholder $placeholder, $text, array $data): string
    {
        if (!isset($data[$placeholder->objectName])) {
            throw new RuntimeException(
                sprintf('The data "%s" is missing.', $placeholder->objectName)
            );
        }

        if (!method_exists($data[$placeholder->objectName], $placeholder->getMethodName())) {
            throw new RuntimeException(sprintf(
                'The data "%s" is missing a getter named "%s".',
                $placeholder->objectName,
                $placeholder->getMethodName()
            ));
        }

        return str_replace(
            $placeholder,
            $this->resolvePlaceholderValue($data[$placeholder->objectName], $placeholder->getMethodName()),
            $text
        );
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

    private function getDependency(ReflectionClass $reflectionClass)
    {
        if (!isset($this->dependencies[$reflectionClass->getName()])) {
            throw new RuntimeException(
                sprintf('The TemplateManager is missing the dependency %s', $reflectionClass->getName())
            );
        }

        return $this->dependencies[$reflectionClass->getName()];
    }
}
