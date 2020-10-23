<?php

declare(strict_types=1);

namespace OpenApiMerge\FileHandling;

use cebe\openapi\SpecObjectInterface;

final class SpecificationFile
{
    private File $file;

    private SpecObjectInterface $openApiSpecificationObject;

    public function __construct(File $filename, SpecObjectInterface $openApiSpecificationObject)
    {
        $this->file                       = $filename;
        $this->openApiSpecificationObject = $openApiSpecificationObject;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function getOpenApiSpecificationObject(): SpecObjectInterface
    {
        return $this->openApiSpecificationObject;
    }
}
