<?php

namespace App\Infrastructure\Mapper;

use App\Domain\Entity\Course;
use App\Infrastructure\Doctrine\Entity\CourseEntity;

class CourseMapper
{
    public static function toEntity(Course $course): CourseEntity
    {
        return new CourseEntity(
            id: $course->id(),
            name: $course->name(),
            description: $course->description(),
        );
    }

    public static function toDomain(CourseEntity $courseEntity): Course
    {
        return new Course(
            id: $courseEntity->id(),
            name: $courseEntity->name(),
            description: $courseEntity->description(),
        );
    }

    public static function updateEntityFromDomain(CourseEntity $courseEntity, Course $course) : CourseEntity
    {
        $courseEntity->name = $course->name();
        $courseEntity->description = $course->description();
        return $courseEntity;
    }
}
