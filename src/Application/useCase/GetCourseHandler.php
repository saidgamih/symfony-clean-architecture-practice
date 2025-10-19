<?php

namespace App\Application\useCase;

use App\Domain\Entity\Course;
use App\Domain\Repository\CourseRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetCourseHandler
{
    public function __construct(private readonly CourseRepositoryInterface $courseRepository)
    {}

    public function handle(string $id) : Course
    {
        $course = $this->courseRepository->findById($id);
        if($course === null) {
            throw new \DomainException("Course not found");
        }

        return $course;
    }
}
