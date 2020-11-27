<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Writer;

use cebe\openapi\Writer;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException;

final class DefinitionWriter implements DefinitionWriterInterface
{
    public function write(SpecificationFile $specFile): string
    {
        switch ($specFile->getFile()->getFileExtension()) {
            case 'json':
                return $this->writeToJson($specFile);

            case 'yml':
            case 'yaml':
                return $this->writeToYaml($specFile);

            default:
                throw InvalidFileTypeException::createFromExtension($specFile->getFile()->getFileExtension());
        }
    }

    public function writeToJson(SpecificationFile $specFile): string
    {
        return Writer::writeToJson($specFile->getOpenApi());
    }

    public function writeToYaml(SpecificationFile $specFile): string
    {
        return Writer::writeToYaml($specFile->getOpenApi());
    }
}
