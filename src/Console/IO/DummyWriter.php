<?php

declare(strict_types=1);

namespace OpenApiMerge\Console\IO;

class DummyWriter implements WriterInterface
{
    /** @var array<int, string> */
    private array $messages = [];

    public function write(string $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return array<int, string>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
