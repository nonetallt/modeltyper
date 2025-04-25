<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Writers\ModelRelationshipWriter;
use ReflectionClass;
use Illuminate\Support\Collection;

class WriteFillables
{
    public function __invoke(
        ReflectionClass $reflectionModel,
        Collection $relations,
        bool $fillableRelations,
        string|null $fillableSuffix,
        ModelRelationshipWriter $relationWriter
    ) : string
    {
        $fillableRelationsType = '';
        $fillableAttributes = $reflectionModel->newInstanceWithoutConstructor()->getFillable();

        if($fillableRelations) {
            // Remove relations from attribute list
            $fillableAttributes = collect($fillableAttributes)->filter(function($attr) use($relations) {
                return $relations->first(fn($relation) => $relation->accessibleViaAttribute($attr)) === null;
            } )->toArray();

            // Create type for fillable relations
            $fillableRelationsType .= ' & {' . PHP_EOL;

            foreach($relations as $relation) {
                $fillableRelationsType .= $relationWriter->setOptional(false)->setSuffix($fillableSuffix)->write($relation) . PHP_EOL;
            }
            $fillableRelationsType .= '}';
        }

        $modelName = $reflectionModel->getShortName();
        $fillablesUnion = implode(' | ', array_map(fn ($fillableAttribute) => "'$fillableAttribute'", $fillableAttributes));

        return "export type {$modelName}{$fillableSuffix} = Pick<$modelName, $fillablesUnion>" . $fillableRelationsType;
    }
}
