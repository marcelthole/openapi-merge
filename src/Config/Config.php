<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Config;

final class Config
{
    private bool $resetComponents         = true;
    private bool $skipResolvingReferences = false;

    public function isResetComponentsEnabled(): bool
    {
        return $this->resetComponents;
    }

    public function resetComponents(bool $resetComponents): self
    {
        $this->resetComponents = $resetComponents;

        return $this;
    }

    public function isSkipResolvingReferencesEnabled(): bool
    {
        return $this->skipResolvingReferences;
    }

    public function skipResolvingReferences(bool $skipResolvingReferences): self
    {
        $this->skipResolvingReferences = $skipResolvingReferences;

        return $this;
    }
}
