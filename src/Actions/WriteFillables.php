<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Writers\ModelRelationshipWriter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;

class WriteFillables
{
    public function __invoke(
        ReflectionClass $reflectionModel,
        Collection $relations,
        bool $useFillableRelations,
        ?string $fillableSuffix,
        ModelRelationshipWriter $relationWriter,
        bool $plurals = false
    ): string {
        $fillableRelationsType = '';
        $fillableAttributes = $reflectionModel->newInstanceWithoutConstructor()->getFillable();

        if ($useFillableRelations) {
            // Remove fillable relations from attribute list
            $fillableAttributes = collect($fillableAttributes)->filter(function ($attr) use ($relations) {
                return $relations->first(fn ($relation) => $relation->isAccessibleViaAttribute($attr)) === null;
            })->toArray();

            // Create type for fillable relations
            $fillableRelationsType .= ' & {' . PHP_EOL;

            foreach ($relations->filter(fn ($relation) => $relation->isFillable()) as $relation) {
                $fillableRelationsType .= $relationWriter->setOptional(false)->setSuffix($fillableSuffix)->write($relation) . PHP_EOL;
            }
            $fillableRelationsType .= '}';
        }

        $modelName = $reflectionModel->getShortName();
        $fillablesUnion = implode(' | ', array_map(fn ($fillableAttribute) => "'$fillableAttribute'", $fillableAttributes));

        $result = "export type {$modelName}{$fillableSuffix} = Pick<$modelName, $fillablesUnion>" . $fillableRelationsType;

        if ($plurals) {
            $result .= PHP_EOL;
            $fillablePlural = Str::plural("{$modelName}{$fillableSuffix}");
            $result .= "export type $fillablePlural = {$modelName}{$fillableSuffix}[]";
        }

        return $result;
    }
}
