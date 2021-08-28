<?php declare(strict_types=1);

namespace App\Entity;

class Placeholder
{
    public string $objectName;
    public string $objectProperty;

    public function __construct(string $objectName, string $objectProperty)
    {
        $this->objectName = $objectName;
        $this->objectProperty = $objectProperty;
    }

    public function getMethodName(): string
    {
        return 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->objectProperty)));
    }

    public function __toString(): string
    {
        return $this->objectName
            ? sprintf('[%s:%s]', $this->objectName, $this->objectProperty)
            : sprintf('[%s]', $this->objectProperty);
    }
}
