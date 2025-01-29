<?php

namespace AllManager\RestBehatExtension\Json;

use Behat\Behat\Context\Argument\ArgumentResolver;

class JsonInspectorResolver implements ArgumentResolver
{
    public function __construct(
        private JsonInspector $jsonInspector,
    ) {
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments): array
    {
        $constructor = $classReflection->getConstructor();
        if (null === $constructor) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (null !== $parameter->getType() && JsonInspector::class === $parameter->getType()->getName()) {
                $arguments[$parameter->name] = $this->jsonInspector;
            }
        }

        return $arguments;
    }
}
