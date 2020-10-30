<?php

declare(strict_types=1);

namespace OpenApiMerge\Reader;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\FileHandling\SpecificationFile;
use OpenApiMerge\Reader\Exception\InvalidFileTypeException;

use function assert;

final class FileReader
{
    public function readFile(File $inputFile): SpecificationFile
    {
        switch ($inputFile->getFileExtension()) {
            case 'yml':
            case 'yaml':
                $spec = Reader::readFromYamlFile($inputFile->getAbsolutePath());
                break;
            case 'json':
                $spec = Reader::readFromJsonFile($inputFile->getAbsolutePath());
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
