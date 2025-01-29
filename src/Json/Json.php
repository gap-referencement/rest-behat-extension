<?php

namespace AllManager\RestBehatExtension\Json;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class Json
{
    private mixed $content;

    public function __construct(
        mixed $content,
        bool $encodedAsString = true,
    ) {
        $this->content = true === $encodedAsString ? $this->decode((string) $content) : $content;
    }

    public static function fromRawContent(mixed $content): self
    {
        return new self($content, false);
    }

    public function read(string $expression, PropertyAccessorInterface $accessor): mixed
    {
        if (is_array($this->content)) {
            $expression = preg_replace('/^root/', '', $expression);
        } else {
            $expression = preg_replace('/^root./', '', $expression);
        }

        // If root asked, we return the entire content
        if (strlen(trim($expression)) <= 0) {
            return $this->content;
        }

        return $accessor->getValue($this->content, $expression);
    }

    public function getRawContent(): mixed
    {
        return $this->content;
    }

    public function encode(bool $pretty = true): false|string
    {
        if (true === $pretty && defined('JSON_PRETTY_PRINT')) {
            return json_encode($this->content, JSON_PRETTY_PRINT);
        }

        return json_encode($this->content);
    }

    public function __toString(): string
    {
        return $this->encode(false);
    }

    private function decode(string $content): mixed
    {
        $result = json_decode($content);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception(sprintf('The string "%s" is not valid json', $content));
        }

        return $result;
    }
}
