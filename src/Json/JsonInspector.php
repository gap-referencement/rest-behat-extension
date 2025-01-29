<?php

namespace AllManager\RestBehatExtension\Json;

class JsonInspector
{
    public function __construct(
        private JsonStorage $jsonStorage,
        private JsonParser $jsonParser,
        private JsonSearcher $jsonSearcher
    ) {
    }

    public function readJsonNodeValue(string $jsonNodeExpression): mixed
    {
        return $this->jsonParser->evaluate(
            $this->readJson(),
            $jsonNodeExpression
        );
    }

    public function searchJsonPath(string $pathExpression): mixed
    {
        return $this->jsonSearcher->search($this->readJson(), $pathExpression);
    }

    public function validateJson(JsonSchema $jsonSchema): void
    {
        $this->jsonParser->validate(
            $this->readJson(),
            $jsonSchema
        );
    }

    public function readJson(): Json
    {
        return $this->jsonStorage->readJson();
    }

    public function writeJson(string $jsonContent): void
    {
        $this->jsonStorage->writeRawContent($jsonContent);
    }
}
