<?php

namespace App\Application\useCase;

use App\Application\DTO\UpdateCourseInputDto;
use App\Domain\Entity\Course;
use App\Domain\Repository\CourseRepositoryInterface;

class UpdateCourseHandler
{
    public function __construct(private readonly CourseRepositoryInterface $repository)
    {}

    public function handle(UpdateCourseInputDto $dto): Course
    {
        $course = $this->repository->findById($dto->id);

        if($course === null){
            throw new \DomainException("Course not found");
        }

        if($dto->name !== null) {
            $course->updateName($dto->name);
        }

        if($dto->description !== null) {
            $course->updateDescription($dto->description);
        }

        $this->repository->save($course);

        return $course;
    }
}
