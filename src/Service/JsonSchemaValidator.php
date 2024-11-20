<?php

namespace App\Service;

use JsonSchema\Validator;
use JsonSchema\Exception\ValidationException;

class JsonSchemaValidator
{
    public function validate(object $data, string $schemaClass): void
    {
        if (!class_exists($schemaClass)) {
            throw new \InvalidArgumentException(sprintf('Schema class not found: %s', $schemaClass));
        }

        if (!method_exists($schemaClass, 'getSchema')) {
            throw new \InvalidArgumentException(sprintf('Schema class must define a static getSchema method: %s', $schemaClass));
        }

        $schema = $schemaClass::getSchema();

        $validator = new Validator();
        $validator->validate($data, (object) $schema);

        if (!$validator->isValid()) {
            $errors = array_map(
                fn($error) => "{$error['property']}: {$error['message']}",
                $validator->getErrors()
            );

            throw new ValidationException(implode(', ', $errors));
        }
    }
}
