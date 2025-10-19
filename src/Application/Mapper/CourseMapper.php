<?php

namespace App\Application\Mapper;

use App\Application\DTO\CourseOutputDto;
use App\Domain\Entity\Course;

class CourseMapper
{
    public static function toDto(Course $course) : CourseOutputDto
    {
        return new CourseOutputDto(
            id: $course->id(),
            name: $course->name(),
            description: $course->description()
        );
    }
}
