<?php

namespace AllManager\RestBehatExtension\Json;

use JsonSchema\SchemaStorageInterface;
use JsonSchema\Validator;

class JsonSchema
{
    public function __construct(
        private string $filename,
        private Validator $validator,
        private SchemaStorageInterface $schemaStorage,
    ) {
    }

    public function validate(Json $json): true
    {
        $schema = $this->schemaStorage->resolveRef('file://'.realpath($this->filename));
        $data = $json->getRawContent();

        $this->validator->validate($data, $schema);

        if (!$this->validator->isValid()) {
            $msg = 'JSON does not validate. Violations:'.PHP_EOL;
            foreach ($this->validator->getErrors() as $error) {
                $msg .= sprintf('  - [%s] %s'.PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }
}
