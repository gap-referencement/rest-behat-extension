<?php

namespace AllManager\RestBehatExtension\Json;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class JsonParser
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
    ) {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor()
        ;
    }

    public function evaluate(Json $json, string $expression): mixed
    {
        $expression = str_replace('->', '.', $expression);

        try {
            return $json->read($expression, $this->propertyAccessor);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Failed to evaluate expression "%s"', $expression), 0, $e);
        }
    }

    public function validate(Json $json, JsonSchema $schema): true
    {
        return $schema->validate($json);
    }
}
