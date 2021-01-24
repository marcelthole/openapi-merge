<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Config;

interface ConfigAwareInterface
{
    public function setConfig(Config $config): void;
}
