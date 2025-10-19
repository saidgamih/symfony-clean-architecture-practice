<?php

namespace App\Application\useCase;

use App\Domain\Repository\CourseRepositoryInterface;

class DeleteCourseHandler
{
    public function __construct(private readonly CourseRepositoryInterface $courseRepository)
    {}

    public function handle(string $id) : void
    {
        $course = $this->courseRepository->findById($id);
        if(!$course) {
            throw new \DomainException('Course not found');
        }

        $this->courseRepository->delete($course);
    }
}
