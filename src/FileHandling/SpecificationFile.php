<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\FileHandling;

use cebe\openapi\spec\OpenApi;

final class SpecificationFile
{
    private File $file;

    private OpenApi $openApi;

    public function __construct(File $filename, OpenApi $openApiSpecificationObject)
    {
        $this->file    = $filename;
        $this->openApi = $openApiSpecificationObject;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}
