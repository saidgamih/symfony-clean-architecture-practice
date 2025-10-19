<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Course;

interface CourseRepositoryInterface
{
    /** @return Course[] */
    public function findAll() : array;
    public function findById(string $id) : ?Course;
    public function save(Course $course) : void;
    public function delete(Course $course) : void;
}
