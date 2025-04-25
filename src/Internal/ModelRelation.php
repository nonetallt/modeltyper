<?php

namespace FumeApp\ModelTyper\Internal;

use Illuminate\Support\Str;
use ReflectionClass;

class ModelRelation
{
    public function __construct(
        private string $name,
        private string $type,
        private string $related
    ) {}

    public static function createFromArray(array $relation): self
    {
        return new self(...$relation);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRelatedModelClass(): string
    {
        return $this->related;
    }

    public function getRelatedModelReflection(): ReflectionClass
    {
        return new ReflectionClass($this->related);
    }

    /**
     * Check if calling getter of specified attribute name will access this relation
     */
    public function accessibleViaAttribute(string $attributeName): bool
    {
        return Str::camel($this->name) === Str::camel($attributeName);
    }
}
