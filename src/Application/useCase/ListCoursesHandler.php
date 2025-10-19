<?php

namespace App\Application\useCase;



use App\Domain\Entity\Course;
use App\Domain\Repository\CourseRepositoryInterface;

class ListCoursesHandler
{
    public function __construct(private readonly CourseRepositoryInterface $courseRepository)
    {}

    /** @return Course[] */
    public function handle() : array
    {
        return $this->courseRepository->findAll();
    }
}
