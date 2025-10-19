<?php

namespace App\Application\DTO;

class UpdateCourseInputDto
{
    public function __construct(public string $id, public ?string $name, public ?string $description)
    {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'] ?? null,
            description: $data['description'] ?? null
        );
    }
}
