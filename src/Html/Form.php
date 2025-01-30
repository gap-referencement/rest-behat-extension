<?php

namespace AllManager\RestBehatExtension\Html;

use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\StreamInterface;

class Form
{
    private string $contentTypeHeaderValue;

    public function __construct(
        private readonly array $body,
    ) {
    }

    public function getBody(): StreamInterface|string
    {
        if ($this->bodyHasFileObject()) {
            return $this->getMultipartStreamBody();
        }

        return $this->getNameValuePairBody();
    }

    public function getContentTypeHeaderValue(): ?string
    {
        return $this->contentTypeHeaderValue;
    }

    private function setContentTypeHeaderValue(string $value): void
    {
        $this->contentTypeHeaderValue = $value;
    }

    private function bodyHasFileObject(): bool
    {
        foreach ($this->body as $element) {
            if ('file' == $element['object']) {
                return true;
            }
        }

        return false;
    }

    private function getMultipartStreamBody(): MultipartStream
    {
        $multipart = array_map(
            function ($element) {
                if ('file' == $element['object']) {
                    return ['name' => $element['name'], 'contents' => fopen($element['value'], 'r')];
                }

                return ['name' => $element['name'], 'contents' => $element['value']];
            },
            $this->body
        );

        $boundary = sha1(uniqid('', true));

        $this->setContentTypeHeaderValue('multipart/form-data; boundary='.$boundary);

        return new MultipartStream($multipart, $boundary);
    }

    private function getNameValuePairBody(): string
    {
        $body = [];
        foreach ($this->body as $element) {
            $body[$element['name']] = $element['value'];
        }

        $this->setContentTypeHeaderValue('application/x-www-form-urlencoded');

        return http_build_query($body);
    }
}
