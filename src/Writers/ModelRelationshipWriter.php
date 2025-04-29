<?php

namespace FumeApp\ModelTyper\Writers;

use FumeApp\ModelTyper\Actions\MatchCase;
use FumeApp\ModelTyper\Internal\ModelRelation;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ModelRelationshipWriter
{
    public function __construct(
        private bool $jsonOutput = false,
        private bool $optionalRelationship = false,
        private bool $plurals = false,
        private ?string $suffix = null
    ) {}

    public function write(ModelRelation $relation): string|array
    {
        $case = Config::get('modeltyper.case.relations', 'snake');
        $relationName = app(MatchCase::class)($case, $relation->getName());
        $relationType = $relation->getType();

        $relatedModel = $relation->getRelatedModelReflection()->getShortName();
        $optional = $this->optionalRelationship ? '?' : '';

        $relationType = match ($relationType) {
            'BelongsToMany', 'HasMany', 'HasManyThrough', 'MorphToMany', 'MorphMany', 'MorphedByMany' => $this->multipleType($relatedModel),
            'BelongsTo', 'HasOne', 'HasOneThrough', 'MorphOne', 'MorphTo' => $this->singularType($relatedModel),
            default => $relatedModel,
        };

        if (in_array($relationType, Config::get('modeltyper.custom_relationships.singular', []))) {
            $relationType = $this->singularType($relationType);
        }

        if (in_array($relationType, Config::get('modeltyper.custom_relationships.plural', []))) {
            $relationType = $this->multipleType($relationType, $this->plurals);
        }

        if ($this->jsonOutput) {
            return [
                'name' => "{$relationName}{$optional}",
                'type' => $relationType,
            ];
        }

        return "  {$relationName}{$optional}: {$relationType}";
    }

    public function setSuffix(string $suffix) : self
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function setOptional(bool $optional): self
    {
        $this->optionalRelationship = $optional;

        return $this;
    }

    private function singularType(string $model)
    {
        return Str::singular($model) . $this->suffix;
    }

    private function multipleType(string $model): string
    {
        if ($this->plurals) {
            return Str::plural($model . $this->suffix);
        }

        return Str::singular($model) . $this->suffix . '[]';
    }
}
