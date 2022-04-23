<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Writer;

use cebe\openapi\Writer;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

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
        return Writer::writeToJson(
            $specFile->getOpenApi(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public function writeToYaml(SpecificationFile $specFile): string
    {
        return Writer::writeToYaml($specFile->getOpenApi());
    }
}
