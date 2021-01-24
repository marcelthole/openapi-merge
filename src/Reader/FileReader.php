<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Reader;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\Config\ConfigAwareInterface;
use Mthole\OpenApiMerge\Config\HasConfig;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;

use function assert;

final class FileReader implements ConfigAwareInterface
{
    use HasConfig;

    public function readFile(File $inputFile): SpecificationFile
    {
        switch ($inputFile->getFileExtension()) {
            case 'yml':
            case 'yaml':
                $spec = Reader::readFromYamlFile(
                    $inputFile->getAbsolutePath(),
                    OpenApi::class,
                    ! $this->getConfig()->isSkipResolvingReferencesEnabled()
                );
                break;
            case 'json':
                $spec = Reader::readFromJsonFile(
                    $inputFile->getAbsolutePath(),
                    OpenApi::class,
                    ! $this->getConfig()->isSkipResolvingReferencesEnabled()
                );
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
