<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\FileHandling;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use Traversable;

use function iterator_to_array;
use function preg_match;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;

class RegexFinder implements Finder
{
    /**
     * @return list<string>
     */
    public function find(string $baseDirectory, string $searchString): array
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $baseDirectory,
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME | RecursiveDirectoryIterator::SKIP_DOTS
        );

        /** @var RecursiveCallbackFilterIterator<string,string, RecursiveDirectoryIterator> $regexIterator */
        $regexIterator = new RecursiveCallbackFilterIterator(
            $directoryIterator,
            static function (
                string $current,
                string $key,
                RecursiveIterator $iterator
            ) use (
                $baseDirectory,
                $searchString
            ) {
                if ($iterator->hasChildren()) {
                    return true;
                }

                $relativeFileName = '.' . substr($current, strlen($baseDirectory));

                return preg_match(
                    sprintf('~%s~i', str_replace('~', '\~', $searchString)),
                    $relativeFileName
                ) === 1;
            }
        );

        /** @var Traversable<string> $recursiveIterator */
        $recursiveIterator = new RecursiveIteratorIterator($regexIterator);

        return iterator_to_array($recursiveIterator, false);
    }
}
