<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use Mthole\OpenApiMerge\Config\ConfigAwareInterface;
use Mthole\OpenApiMerge\Config\HasConfig;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Reader\FileReader;

use function array_merge;

class OpenApiMerge implements OpenApiMergeInterface, ConfigAwareInterface
{
    use HasConfig;

    private FileReader $openApiReader;

    public function __construct(FileReader $openApiReader)
    {
        $this->openApiReader = $openApiReader;
    }

    public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile
    {
        $this->openApiReader->setConfig($this->getConfig());
        $mergedOpenApiDefinition = $this->openApiReader->readFile($baseFile)->getOpenApi();

        foreach ($additionalFiles as $additionalFile) {
            $additionalDefinition = $this->openApiReader->readFile($additionalFile)->getOpenApi();

            foreach ($additionalDefinition->paths->getPaths() as $name => $path) {
                if (!$mergedOpenApiDefinition->paths->hasPath($name)) {
                    $mergedOpenApiDefinition->paths->addPath($name, $path);

                    continue;
                }

                $operations = array_merge(
                    $mergedOpenApiDefinition->paths->getPath($name)->getOperations(),
                    $path->getOperations()
                );

                $mergedOpenApiDefinition->paths->removePath($name);
                $mergedOpenApiDefinition->paths->addPath($name, new PathItem($operations));
            }
        }

        if ($this->getConfig()->isResetComponentsEnabled() && $mergedOpenApiDefinition->components !== null) {
            $mergedOpenApiDefinition->components->schemas = [];
        }

        return new SpecificationFile(
            $baseFile,
            $mergedOpenApiDefinition
        );
    }
}
