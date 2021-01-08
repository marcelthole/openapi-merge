<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Reader;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;

use function assert;

final class FileReader
{
    public function readFile(File $inputFile): SpecificationFile
    {
        switch ($inputFile->getFileExtension()) {
            case 'yml':
            case 'yaml':
                $spec = Reader::readFromYamlFile($inputFile->getAbsolutePath(), OpenApi::class);
                break;
            case 'json':
                $spec = Reader::readFromJsonFile($inputFile->getAbsolutePath(), OpenApi::class);
                break;
            default:
                throw InvalidFileTypeException::createFromExtension($inputFile->getFileExtension());
        }

        assert($spec instanceof OpenApi);

        return new SpecificationFile(
            $inputFile,
            $spec
        );
    }
}
