<?php

namespace Mthole\OpenApiMerge\Reader;

use cebe\openapi\Reader;
use cebe\openapi\SpecObjectInterface;

class OpenApiReaderWrapper
{
    public function readFromYamlFile(string $fileName, string $baseType, bool $resolveReferences): SpecObjectInterface
    {
        return Reader::readFromYamlFile($fileName, $baseType, $resolveReferences);
    }

    public function readFromJsonFile(string $fileName, string $baseType, bool $resolveReferences): SpecObjectInterface
    {
        return Reader::readFromJsonFile($fileName, $baseType, $resolveReferences);
    }
}
