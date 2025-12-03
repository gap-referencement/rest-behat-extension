<?php

namespace AllManager\RestBehatExtension;

abstract class ExpectationFailed extends \Exception implements \Stringable
{
    abstract public function getContextText(): false|string;

    public function __toString(): string
    {
        try {
            $contextText = $this->pipeString($this->trimString($this->getContextText())."\n");
            $string = sprintf("%s\n\n%s", $this->getMessage(), $contextText);
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }

    protected function pipeString(string $string): string
    {
        return '|  '.strtr($string, ["\n" => "\n|  "]);
    }

    protected function trimString(string $string, int $count = 1000): string
    {
        $string = trim($string);
        if ($count < mb_strlen($string)) {
            return mb_substr($string, 0, $count - 3).'...';
        }

        return $string;
    }
}
