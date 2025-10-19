<?php

namespace App\Application\DTO;

class CourseOutputDto
{
    public function __construct(public string $id, public string $name, public string $description)
    {}
}
