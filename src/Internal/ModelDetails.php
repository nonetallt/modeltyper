<?php

namespace FumeApp\ModelTyper\Internal;

use Illuminate\Support\Collection;
use ReflectionClass;

class ModelDetails
{
    public function __construct(
        private ReflectionClass $reflection,
        private Collection $columnAttributes,
        private Collection $nonColumnAttributes,
        private Collection $relations,
        private Collection $interfaces,
        private Collection $imports
    )
    {
    }

    public function getName() : string
    {
        return $this->reflection->getShortName();
    }

    public function getReflectionClass() : ReflectionClass
    {
        return $this->reflection;
    }

    public function getColumnAttributes() : Collection
    {
        return $this->columnAttributes;
    }

    public function getNonColumnAttributes() : Collection
    {
        return $this->nonColumnAttributes;
    }

    /**
     * @return Collection<int, ModelRelation>
     */
    public function getRelations() : Collection
    {
        return $this->relations;
    }

    public function getIntefaces() : Collection
    {
        return $this->interfaces;
    }

    public function getImports() : Collection
    {
        return $this->imports;
    }
}
