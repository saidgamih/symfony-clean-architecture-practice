<?php

namespace App\Application\useCase;

use App\Application\DTO\RegisterCourseInputDto;
use App\Domain\Entity\Course;
use App\Domain\Repository\CourseRepositoryInterface;
use Symfony\Component\Uid\Uuid;

class RegisterCourseHandler
{
    public function __construct(private CourseRepositoryInterface $courseRepository)
    {}

    public function handle(RegisterCourseInputDto $dto) : Course
    {
        $course = new Course(id : Uuid::v4()->toString(), name: $dto->name, description: $dto->description);
        $this->courseRepository->save($course);

        return $course;
    }
}
