<?php

namespace App\Api\Controller;

use App\Application\DTO\RegisterCourseInputDto;
use App\Application\DTO\UpdateCourseInputDto;
use App\Application\Mapper\CourseMapper;
use App\Application\useCase\DeleteCourseHandler;
use App\Application\useCase\GetCourseHandler;
use App\Application\useCase\ListCoursesHandler;
use App\Application\useCase\RegisterCourseHandler;
use App\Application\useCase\UpdateCourseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/courses')]
class CourseController extends AbstractController
{
    #[Route('', name: 'course_list', methods: ['GET'])]
    public function list(ListCoursesHandler $handler): JsonResponse
    {
        $courses = $handler->handle();
        return new JsonResponse(array_map(fn($course) => CourseMapper::toDto($course), $courses));
    }

    #[Route('/{id}', name: 'course_get', methods: ['GET'])]
    public function get(string $id, GetCourseHandler $handler): JsonResponse
    {
        try {
            $course = $handler->handle($id);
            return new JsonResponse(CourseMapper::toDto($course));
        } catch (\DomainException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'course_register', methods: ['POST'])]
    public function register(Request $request, RegisterCourseHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $courseInputDto = RegisterCourseInputDto::fromArray($data);
        $course = $handler->handle($courseInputDto);

        return new JsonResponse(CourseMapper::toDto($course), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'course_update', methods: ['PUT'])]
    public function update(string $id, Request $request, UpdateCourseHandler $handler): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $data['id'] = $id;
        $courseInputDto = UpdateCourseInputDto::fromArray($data);

        try {
            $course = $handler->handle($courseInputDto);
            return new JsonResponse(CourseMapper::toDto($course));
        } catch (\DomainException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('/{id}', name: 'course_delete', methods: ['DELETE'])]
    public function delete(string $id, DeleteCourseHandler $handler) : JsonResponse
    {
        try {
            $handler->handle($id);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\DomainException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
