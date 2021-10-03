<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Reader;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;

final class FileReader
{
    private OpenApiReaderWrapper $openApiReader;

    public function __construct(?OpenApiReaderWrapper $openApiReader = null)
    {
        $this->openApiReader = $openApiReader ?? new OpenApiReaderWrapper();
    }

    public function readFile(File $inputFile, bool $resolveReferences = true): SpecificationFile
    {
        switch ($inputFile->getFileExtension()) {
            case 'yml':
            case 'yaml':
                $spec = $this->openApiReader->readFromYamlFile(
                    $inputFile->getAbsoluteFile(),
                    OpenApi::class,
                    $resolveReferences
                );
                break;
            case 'json':
                $spec = $this->openApiReader->readFromJsonFile(
                    $inputFile->getAbsoluteFile(),
                    OpenApi::class,
                    $resolveReferences
                );
                break;
            default:
                throw InvalidFileTypeException::createFromExtension($inputFile->getFileExtension());
        }

        return new SpecificationFile(
            $inputFile,
            $spec
        );
    }
}
