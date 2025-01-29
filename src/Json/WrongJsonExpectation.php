<?php

namespace AllManager\RestBehatExtension\Json;

use AllManager\RestBehatExtension\ExpectationFailed;

class WrongJsonExpectation extends ExpectationFailed
{
    public function __construct(
        string $message,
        private Json $json,
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getContextText(): false|string
    {
        return $this->json->encode();
    }
}
