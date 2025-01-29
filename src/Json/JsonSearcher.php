<?php

namespace AllManager\RestBehatExtension\Json;

use JmesPath\Env;

class JsonSearcher
{
    public function search(Json $json, string $pathExpression): mixed
    {
        return Env::search($pathExpression, $json->getRawContent());
    }
}
