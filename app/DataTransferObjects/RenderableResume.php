<?php

namespace App\DataTransferObjects;

class RenderableResume
{
    /**
     * @param array<string,mixed> $meta
     * @param array<string,mixed> $personal
     * @param array<int,array<string,mixed>> $sections
     */
    public function __construct(
        public readonly array $meta,
        public readonly array $personal,
        public readonly array $sections,
    ) {}

    public function toArray(): array
    {
        return [
            'meta' => $this->meta,
            'personal' => $this->personal,
            'sections' => $this->sections,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
