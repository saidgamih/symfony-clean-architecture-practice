<?php

namespace App\Infrastructure\Doctrine\Repository;

use App\Infrastructure\Mapper\CourseMapper;
use App\Domain\Entity\Course;
use App\Domain\Repository\CourseRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\CourseEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class DoctrineCourseRepository implements CourseRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    public function findAll() : array
    {
        $entities = $this->entityManager->getRepository(CourseEntity::class)->findAll();
        return array_map(fn(CourseEntity $entity) => CourseMapper::toDomain($entity), $entities);
    }

    public function save(Course $course): void
    {
        $entity = $this->entityManager->find(CourseEntity::class, $course->id());
        if ($entity) {
            $course = CourseMapper::updateEntityFromDomain($entity, $course);
        } else {
            $course = CourseMapper::toEntity($course);
        }

        $this->entityManager->persist($course);
        $this->entityManager->flush();
    }

    public function findById(string $id) : ?Course
    {
        $course = $this->entityManager->getRepository(CourseEntity::class)->find($id);
        if (!$course) {
            return null;
        }
        return CourseMapper::toDomain($course);
    }


    public function delete(Course $course) : void
    {
        $entity = $this->entityManager->find(CourseEntity::class, $course->id());
        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();
        }
    }
}
