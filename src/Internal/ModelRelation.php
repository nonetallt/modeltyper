<?php

namespace FumeApp\ModelTyper\Internal;

use Illuminate\Support\Str;
use ReflectionClass;

class ModelRelation
{
    private static array $fillables = [];

    public function __construct(
        private ReflectionClass $model,
        private string $name,
        private string $type,
        private string $related
    ) {}

    public static function createFromArray(array $relation): self
    {
        return new self(...$relation);
    }

    /**
     * Get the relationship name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the relationship class (BelongsTo etc.)
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the name of the model class that defined this relation
     */
    public function getDefiningModelClass(): string
    {
        return $this->model->getName();
    }

    /**
     * Get the type of model returned by the relationship
     */
    public function getRelatedModelClass(): string
    {
        return $this->related;
    }

    public function getRelatedModelReflection(): ReflectionClass
    {
        return new ReflectionClass($this->related);
    }

    /**
     * Check if this relation is accessible via a fillable attribute
     */
    public function isFillable(): bool
    {
        foreach ($this->getFillables() as $fillableAttribute) {
            if ($this->isAccessibleViaAttribute($fillableAttribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if calling getter of specified attribute name will access this relation
     */
    public function isAccessibleViaAttribute(string $attributeName): bool
    {
        return Str::camel($this->name) === Str::camel($attributeName);
    }

    /**
     * Get a list of fillables of the parent model.
     */
    private function getFillables()
    {
        $key = $this->model->getName();
        if (! array_key_exists($key, self::$fillables)) {
            // Resolve fillables for owner model
            // The fillables are cached on per model basis to avoid redundant repeat constructions
            self::$fillables[$key] = $this->model->newInstanceWithoutConstructor()->getFillable();
        }

        return self::$fillables[$key];
    }
}
