<?php

namespace App\Application\DTO;

class RegisterCourseInputDto
{
    public function __construct(public string $name, public string $description)
    {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? ''
        );
    }
}
