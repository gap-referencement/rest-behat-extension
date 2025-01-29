<?php

namespace AllManager\RestBehatExtension\Json;

class JsonStorage
{
    private ?string $rawContent = null;

    public function writeRawContent(string $rawContent): void
    {
        $this->rawContent = $rawContent;
    }

    public function readJson(): Json
    {
        if (null === $this->rawContent) {
            throw new \LogicException('No content defined. You should use JsonStorage::writeRawContent method to inject content you want to analyze');
        }

        return new Json($this->rawContent);
    }
}
