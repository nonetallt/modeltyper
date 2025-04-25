<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Traits\ClassBaseName;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class WriteRelationship
{
    use ClassBaseName;

    /**
     * Write the relationship to the output.
     *
     * @param  array{name: string, type: string, related:string}  $relation
     * @return array{type: string, name: string}|string
     */
    public function __invoke(array $relation, string $indent = '', bool $jsonOutput = false, bool $optionalRelation = false, bool $plurals = false, ?string $suffix = null): array|string
    {
        $case = Config::get('modeltyper.case.relations', 'snake');
        $name = app(MatchCase::class)($case, $relation['name']);

        $relatedModel = $this->getClassName($relation['related']);
        $optional = $optionalRelation ? '?' : '';

        $relationType = match ($relation['type']) {
            'BelongsToMany', 'HasMany', 'HasManyThrough', 'MorphToMany', 'MorphMany', 'MorphedByMany' => $this->multipleType($relatedModel, $plurals, $suffix),
            'BelongsTo', 'HasOne', 'HasOneThrough', 'MorphOne', 'MorphTo' => Str::singular($relatedModel),
            default => $relatedModel,
        };

        if (in_array($relation['type'], Config::get('modeltyper.custom_relationships.singular', []))) {
            $relationType = Str::singular($relation['type']);
        }

        if (in_array($relation['type'], Config::get('modeltyper.custom_relationships.plural', []))) {
            $relationType = $this->multipleType($relation['type'], $plurals, $suffix);
        }

        if ($jsonOutput) {
            return [
                'name' => "{$name}{$optional}",
                'type' => $relationType,
            ];
        }

        return "{$indent}  {$name}{$optional}: {$relationType}" . PHP_EOL;
    }

    private function singularType(string $model, ?string $suffix)
    {
        return Str::singular($model) . $suffix;
    }

    private function multipleType(string $model, bool $pluralize, ?string $suffix): string
    {
        if ($pluralize) {
            return Str::plural($model) . $suffix;
        }

        return Str::singular($model) . $suffix . '[]';
    }
}
