<?php

namespace AllManager\RestBehatExtension\Rest;

use Behat\Behat\Context\Argument\ArgumentResolver;

class ApiBrowserResolver implements ArgumentResolver
{
    public function __construct(
        private ApiBrowser $apiBrowser,
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
            if (null !== $parameter->getType() && 'AllManager\RestBehatExtension\Rest\ApiBrowser' === $parameter->getType()->getName()) {
                $arguments[$parameter->name] = $this->apiBrowser;
            }
        }

        return $arguments;
    }
}
