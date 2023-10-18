<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Util;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

class Json
{
    /** @return array<mixed> */
    public static function toArray(mixed $data): array
    {
        return (array) json_decode(json_encode($data, JSON_THROW_ON_ERROR) ?: '[]', true);
    }
}
