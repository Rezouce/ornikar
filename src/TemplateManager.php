<?php declare(strict_types=1);

namespace App;

use App\Context\ApplicationContext;
use App\Entity\Placeholder;
use App\Entity\Template;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

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
