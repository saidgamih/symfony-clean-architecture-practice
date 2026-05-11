# Symfony Clean Architecture Practice

A comprehensive implementation of **Clean Architecture** principles using Symfony 7.3, API Platform, and Doctrine ORM. This repository demonstrates how to structure a PHP application with excellent separation of concerns and decoupling.

## 📋 Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Project Structure](#project-structure)
- [Separation of Concerns](#separation-of-concerns)
- [Dependency Flow & Decoupling](#dependency-flow--decoupling)
- [Request Flow Example](#request-flow-example)
- [Decoupling Analysis](#decoupling-analysis)
- [Key Concepts](#key-concepts)
- [Getting Started](#getting-started)
- [Technologies](#technologies)

## Overview

This project implements a **layered clean architecture** pattern that achieves excellent **separation of concerns** and **decoupling**. The architecture ensures:

- ✅ **Framework Independence**: Domain logic has zero framework dependencies
- ✅ **Database Independence**: Can swap Doctrine for any persistence layer
- ✅ **API Independence**: Can add CLI, gRPC, or other interfaces without changes
- ✅ **Testability**: Business logic is testable without mocks
- ✅ **Maintainability**: Clear layer boundaries with minimal cross-layer coupling

## Architecture

### Clean Architecture Layers

The application follows the **Clean Architecture** pattern with 4 distinct layers:

```
┌─────────────────────────────────────────────────────────────┐
│  Presentation Layer (API/Controller)                         │
│  - HTTP handling, routing, JSON serialization               │
└─────────────────────────────────────────────────────────────┘
                            ↓ depends on
┌─────────────────────────────────────────────────────────────┐
│  Application Layer (Use Cases, DTOs)                         │
│  - Business process orchestration, DTO mapping              │
└─────────────────────────────────────────────────────────────┘
                            ↓ depends on
┌─────────────────────────────────────────────────────────────┐
│  Domain Layer (Entities, Interfaces)                         │
│  - Core business rules, validation, pure logic              │
└─────────────────────────────────────────────────────────────┘
                            ↓ depends on
┌─────────────────────────────────────────────────────────────┐
│  Infrastructure Layer (Database, ORM)                        │
│  - Doctrine ORM, repository implementations                 │
└─────────────────────────────────────────────────────────────┘
```

### The Dependency Rule

**Dependencies point inward** — outer layers depend on inner layers, **never the reverse**.

## Project Structure

```
src/
├── Domain/                          # ⭐ CORE BUSINESS LOGIC (innermost)
│   ├── Entity/
│   │   └── Course.php              # Pure business domain model
│   └── Repository/
│       └── CourseRepositoryInterface.php  # Domain interface (abstraction)
│
├── Application/                     # 🎯 APPLICATION LAYER (orchestration)
│   ├── useCase/                    # Business use case handlers
│   │   ├── RegisterCourseHandler.php
│   │   ├── GetCourseHandler.php
│   │   ├── ListCoursesHandler.php
│   │   ├── UpdateCourseHandler.php
│   │   └── DeleteCourseHandler.php
│   ├── DTO/                        # Data Transfer Objects (input/output)
│   │   ├── RegisterCourseInputDto.php
│   │   ├── UpdateCourseInputDto.php
│   │   └── CourseOutputDto.php
│   └── Mapper/                     # DTO ↔ Domain Entity mapping
│       └── CourseMapper.php
│
├── Infrastructure/                 # 🔧 INFRASTRUCTURE LAYER (tools & frameworks)
│   ├── Doctrine/                   # Database persistence implementation
│   │   ├── Entity/                # Doctrine ORM entities (persistence model)
│   │   │   └── CourseEntity.php
│   │   └── Repository/            # Repository implementations (Domain interface)
│   │       └── DoctrineCourseRepository.php
│   └── Mapper/                     # Domain Entity ↔ ORM Entity mapping
│       └── CourseMapper.php
│
├── Api/                            # 💻 PRESENTATION LAYER (HTTP interface)
│   └── Controller/
│       └── CourseController.php
│
└── Kernel.php                      # Symfony Kernel entry point
```

## Separation of Concerns

Each layer has a **specific responsibility** and should not be mixed:

| Layer | Responsibility | Example |
|-------|-----------------|---------|
| **Domain** | Core business rules, validation, entities | `Course` entity with business logic |
| **Application** | Use case orchestration, DTO mapping, transactions | `RegisterCourseHandler` coordinates the flow |
| **Infrastructure** | Database, ORM, external tools, persistence | Doctrine ORM implementation |
| **Api** | HTTP requests/responses, routing, serialization | Controller handles HTTP endpoints |

### Why Separation Matters

1. **Testability**: Test business logic without database or framework
2. **Reusability**: Use domain logic across multiple interfaces (API, CLI, jobs)
3. **Maintainability**: Changes in one layer don't ripple through others
4. **Flexibility**: Swap implementations without changing interfaces
5. **Scalability**: Independent layers can be tested and deployed separately

## Dependency Flow & Decoupling

### The Dependency Direction

Dependencies flow **inward**, creating a stable core:

```
Controller (Api)
    ↓ uses
Handlers (Application)
    ↓ uses
Repository Interface (Domain)
    ↓ implemented by
Doctrine Repository (Infrastructure)
```

### Key Decoupling Strategies

#### 1. **Repository Pattern (Interface Segregation)**

```php
// Domain Layer - defines the contract
namespace App\Domain\Repository;

interface CourseRepositoryInterface
{
    public function findAll() : array;
    public function findById(string $id) : ?Course;
    public function save(Course $course) : void;
    public function delete(Course $course) : void;
}
```

```php
// Application Layer - depends on the interface
namespace App\Application\useCase;

class RegisterCourseHandler
{
    public function __construct(private CourseRepositoryInterface $courseRepository)
    {}

    public function handle(RegisterCourseInputDto $dto) : Course
    {
        $course = new Course(Uuid::v4()->toString(), $dto->name, $dto->description);
        $this->courseRepository->save($course);  // Uses interface, not implementation
        return $course;
    }
}
```

```php
// Infrastructure Layer - implements the interface
namespace App\Infrastructure\Doctrine\Repository;

class DoctrineCourseRepository implements CourseRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    public function save(Course $course): void
    {
        $entity = CourseMapper::toEntity($course);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
    // ... other methods
}
```

**Benefit**: Can replace Doctrine with MongoDB or any other persistence layer without touching domain or application code.

---

#### 2. **DTO (Data Transfer Objects) - Boundary Protection**

**Input DTO** (API to Application):
```php
namespace App\Application\DTO;

class RegisterCourseInputDto
{
    public function __construct(public string $name, public string $description)
    {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? ''
        );
    }
}
```

**Output DTO** (Domain to API):
```php
class CourseOutputDto
{
    public function __construct(public string $id, public string $name, public string $description)
    {}
}
```

**Benefit**: API contracts don't leak into domain logic. Can change API response format without touching business logic.

---

#### 3. **Mapper Pattern - Layer Translation**

**Application Mapper** (Domain ↔ DTO):
```php
namespace App\Application\Mapper;

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
```

**Infrastructure Mapper** (Domain ↔ ORM Entity):
```php
namespace App\Infrastructure\Mapper;

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
}
```

**Benefit**: Domain never directly touches DTOs or ORM entities. Each layer maintains its own representation.

---

#### 4. **Domain Entity - Framework Agnostic**

```php
namespace App\Domain\Entity;

class Course
{
    public function __construct(
        private string $id,
        private string $name,
        private string $description = ""
    ) {}

    public function id() : string {
        return $this->id;
    }

    public function name() : string {
        return $this->name;
    }

    public function description() : ?string {
        return $this->description;
    }

    // Business rule validation - no framework dependency
    public function updateName(string $name) : void
    {
        if(strlen($name) < 4) {
            throw new \DomainException("Name can't be less than 4 characters");
        }
        $this->name = $name;
    }

    public function updateDescription(string $description) : void
    {
        $this->description = $description;
    }
}
```

**Key Points**:
- Zero Symfony dependencies
- Zero Doctrine ORM annotations
- Pure business logic
- Can be tested independently

```php
// Test without any framework setup
$course = new Course('uuid-123', 'PHP 101');
$course->updateName('PHP Advanced');  // ✅ Works!

$course->updateName('AB');  // ❌ Throws DomainException
```

---

## Request Flow Example

Let's trace a **POST /api/courses** (register course) request through all layers:

### Step 1: Presentation Layer - HTTP Entry Point

```php
namespace App\Api\Controller;

#[Route('/api/courses', methods: ['POST'])]
public function register(Request $request, RegisterCourseHandler $handler): JsonResponse
{
    // 1. Parse incoming JSON to DTO
    $data = json_decode($request->getContent(), true);
    $courseInputDto = RegisterCourseInputDto::fromArray($data);
    
    // 2. Delegate to application layer
    $course = $handler->handle($courseInputDto);
    
    // 3. Transform domain entity to output DTO for JSON response
    return new JsonResponse(CourseMapper::toDto($course), Response::HTTP_CREATED);
}
```

**Responsibility**: HTTP handling, JSON serialization, error mapping

---

### Step 2: Application Layer - Use Case Orchestration

```php
namespace App\Application\useCase;

class RegisterCourseHandler
{
    // Dependency Injection - depends on interface, not implementation
    public function __construct(private CourseRepositoryInterface $courseRepository)
    {}

    public function handle(RegisterCourseInputDto $dto) : Course
    {
        // 1. Create domain entity (instantiate business logic)
        $course = new Course(
            id: Uuid::v4()->toString(),
            name: $dto->name,
            description: $dto->description
        );
        
        // 2. Persist via abstracted repository (no knowledge of database)
        $this->courseRepository->save($course);
        
        // 3. Return domain entity to presentation layer
        return $course;
    }
}
```

**Responsibility**: Orchestrating the business use case, no business logic implementation

---

### Step 3: Domain Layer - Business Logic

```php
namespace App\Domain\Entity;

class Course
{
    public function __construct(private string $id, private string $name, private string $description = "")
    {}

    // Business rule: name must be at least 4 characters
    public function updateName(string $name) : void
    {
        if(strlen($name) < 4) {
            throw new \DomainException("Name can't be less than 4 characters");
        }
        $this->name = $name;
    }
}
```

**Responsibility**: Pure business logic, validation, state management

---

### Step 4: Infrastructure Layer - Persistence Implementation

```php
namespace App\Infrastructure\Doctrine\Repository;

class DoctrineCourseRepository implements CourseRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {}

    public function save(Course $course): void
    {
        // 1. Check if entity already exists
        $entity = $this->entityManager->find(CourseEntity::class, $course->id());
        
        // 2. Map domain entity to ORM entity
        if ($entity) {
            $entity = CourseMapper::updateEntityFromDomain($entity, $course);
        } else {
            $entity = CourseMapper::toEntity($course);
        }

        // 3. Persist using Doctrine ORM
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
```

**Responsibility**: Database-specific details, ORM configuration, persistence

---

## Decoupling Analysis

### Decoupling Level: Excellent (9/10)

| Aspect | Level | Evidence |
|--------|-------|----------|
| **Framework Independence** | ⭐⭐⭐⭐⭐ | Domain has zero Symfony dependencies |
| **Database Independence** | ⭐⭐⭐⭐⭐ | Doctrine fully isolated in Infrastructure layer |
| **API Independence** | ⭐⭐⭐⭐⭐ | Can add CLI/gRPC/GraphQL without touching domain |
| **Testability** | ⭐⭐⭐⭐⭐ | Business logic testable without framework or database |
| **Maintainability** | ⭐⭐⭐⭐☆ | Clear layer boundaries; minimal cross-layer coupling |

### Why This Matters

#### Before Clean Architecture (Tightly Coupled)
```
❌ Domain depends on Doctrine ORM
❌ Domain depends on Symfony
❌ Business logic scattered across controllers
❌ Hard to test without database
❌ Framework changes break everything
```

#### After Clean Architecture (Decoupled)
```
✅ Domain is framework-agnostic
✅ Domain is database-agnostic
✅ Business logic centralized and reusable
✅ Test without framework or database
✅ Swap frameworks/databases easily
```

### Example: Swapping Persistence Layers

**Current**: Using Doctrine ORM
```php
// Register the current implementation in Symfony config
class DoctrineCourseRepository implements CourseRepositoryInterface { }
```

**Future**: Switch to MongoDB
```php
// Create new implementation, update config, domain is unchanged!
class MongoCourseRepository implements CourseRepositoryInterface { }
```

The entire domain layer and application layer remain **completely unchanged**. Only infrastructure layer is replaced.

---

## Key Concepts

### 1. **Dependency Inversion Principle (DIP)**
- High-level modules (domain) don't depend on low-level modules (infrastructure)
- Both depend on abstractions (interfaces)

### 2. **Single Responsibility Principle (SRP)**
- Each class has one reason to change
- Domain changes for business reasons
- Infrastructure changes for database reasons

### 3. **Interface Segregation Principle (ISP)**
- Domain defines minimal, focused interfaces
- Infrastructure implements complete interfaces
- Application depends only on what it needs

### 4. **Open/Closed Principle (OCP)**
- Open for extension (add new repository implementations)
- Closed for modification (don't change existing domain)

### 5. **Ports & Adapters Pattern**
- Domain defines ports (interfaces)
- Infrastructure provides adapters (implementations)
- Application uses ports, framework provides adapters

---

## Getting Started

### Prerequisites
- PHP >= 8.2
- Docker & Docker Compose
- Composer

### Installation

```bash
# Clone the repository
git clone https://github.com/saidgamih/symfony-clean-architecture-practice.git
cd symfony-clean-architecture-practice

# Install dependencies
composer install

# Set up environment
cp .env.dev .env

# Start Docker containers
docker-compose up -d

# Run migrations
docker-compose exec app php bin/console doctrine:migrations:migrate
```

### API Endpoints

```bash
# List all courses
GET /api/courses

# Get a specific course
GET /api/courses/{id}

# Create a new course
POST /api/courses
{
    "name": "PHP Clean Architecture",
    "description": "Learn clean architecture principles"
}

# Update a course
PUT /api/courses/{id}
{
    "name": "Advanced PHP",
    "description": "Updated description"
}

# Delete a course
DELETE /api/courses/{id}
```

### Running Tests

```bash
# Unit tests (business logic)
docker-compose exec app php bin/phpunit

# Integration tests
docker-compose exec app php bin/phpunit --testsuite=integration
```

---

## Technologies

### Core Framework
- **Symfony 7.3**: Modern PHP web framework
- **PHP 8.2+**: Latest PHP version with strong typing

### API & Database
- **API Platform 4.2**: REST/GraphQL API framework
- **Doctrine ORM 3.5**: Object-Relational Mapping
- **Doctrine Migrations**: Database version control

### Utilities
- **CORS Bundle**: Cross-Origin Resource Sharing
- **Security Bundle**: Authentication & Authorization
- **Serializer**: Data serialization/deserialization
- **Validator**: Data validation
- **Twig**: Template engine

### Development
- **Docker & Docker Compose**: Containerization
- **Symfony Maker Bundle**: Code generation

---

## Architecture Benefits

### 1. **Testability**
```php
// Test domain logic without framework or database
$course = new Course('uuid', 'PHP 101');
$course->updateName('PHP Advanced');  // Works!
$course->updateName('AB');  // Throws exception - testable!
```

### 2. **Reusability**
```php
// Use handlers across multiple interfaces
$handler = new RegisterCourseHandler($repository);
$handler->handle($dto);  // Works for API, CLI, Jobs, etc.
```

### 3. **Maintainability**
```php
// Clear structure - easy to find and modify code
// Domain logic → src/Domain/Entity/
// Use cases → src/Application/useCase/
// Database → src/Infrastructure/Doctrine/
```

### 4. **Scalability**
```php
// Each layer can be optimized independently
// Can add caching at infrastructure layer
// Can add queuing at application layer
// Can add validation at API layer
```

### 5. **Framework Flexibility**
```php
// Domain is not tied to Symfony
// Can use in Laravel, WordPress, standalone CLI, etc.
```

---

## Further Reading

- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Domain-Driven Design by Eric Evans](https://www.domainlanguage.com/ddd/)
- [Hexagonal Architecture (Ports & Adapters)](https://en.wikipedia.org/wiki/Hexagonal_architecture_(software))
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

## License

This project is proprietary. See LICENSE file for details.

## Author

[saidgamih](https://github.com/saidgamih)

---

**Happy Learning! 🚀**

This is a practice repository designed to help understand and implement clean architecture principles. Feel free to fork, modify, and learn!
