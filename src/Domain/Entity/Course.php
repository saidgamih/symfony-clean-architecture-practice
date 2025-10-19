<?php

namespace App\Domain\Entity;

class Course
{
    public function __construct(private string $id, private string $name, private string $description = "")
    {}

    public function id() : string {
        return $this->id;
    }

    public function name() : string {
        return $this->name;
    }

    public function description() : ?string
    {
        return $this->description;
    }

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
