<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Config;

trait HasConfig
{
    private Config $config;

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): Config
    {
        return $this->config ?? $this->config = new Config();
    }
}
