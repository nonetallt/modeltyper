<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Internal\ModelDetails;
use FumeApp\ModelTyper\Internal\ModelRelation;
use FumeApp\ModelTyper\Traits\ModelRefClass;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionException;

class BuildModelDetails
{
    use ModelRefClass;

    /**
     * Build the model details.
     *
     *
     * @throws ReflectionException
     */
    public function __invoke(ReflectionClass|string $modelClass): ?ModelDetails
    {
        $modelDetails = $this->getModelDetails($modelClass);

        if ($modelDetails === null) {
            return null;
        }

        $reflectionModel = $this->getRefInterface($modelDetails);
        $laravelModel = $reflectionModel->newInstance();
        $databaseColumns = $laravelModel->getConnection()->getSchemaBuilder()->getColumnListing($laravelModel->getTable());

        $columns = collect($modelDetails['attributes'])->filter(fn ($att) => in_array($att['name'], $databaseColumns));
        $nonColumns = collect($modelDetails['attributes'])->filter(fn ($att) => ! in_array($att['name'], $databaseColumns));
        $relations = collect($modelDetails['relations']);

        $interfaces = collect($laravelModel->interfaces ?? [])->map(fn ($interface, $key) => [
            'name' => $key,
            'type' => $interface['type'] ?? 'unknown',
            'nullable' => $interface['nullable'] ?? false,
            'import' => $interface['import'] ?? null,
            'forceType' => true,
        ]);

        $imports = $interfaces
            ->filter(fn (array $interface): bool => isset($interface['import']))
            ->map(fn (array $interface): array => ['import' => $interface['import'], 'type' => $interface['type']])
            ->unique()
            ->values();

        // Override all columns, mutators and relationships with custom interfaces
        $columns = $this->overrideCollectionWithInterfaces($columns, $interfaces);

        $nonColumns = $this->overrideCollectionWithInterfaces($nonColumns, $interfaces);

        $relations = $this->overrideCollectionWithInterfaces($relations, $interfaces);

        return new ModelDetails(
            reflection: $reflectionModel,
            columnAttributes: $columns,
            nonColumnAttributes: $nonColumns,
            relations: $relations->map(fn ($relation) => ModelRelation::createFromArray($relation)),
            interfaces: $interfaces,
            imports: $imports
        );
    }

    /**
     * @return array{"class": class-string<\Illuminate\Database\Eloquent\Model>, database: string, table: string, policy: class-string|null, attributes: \Illuminate\Support\Collection, relations: \Illuminate\Support\Collection, events: \Illuminate\Support\Collection, observers: \Illuminate\Support\Collection, collection: class-string<\Illuminate\Database\Eloquent\Collection<\Illuminate\Database\Eloquent\Model>>, builder: class-string<\Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>>}|null
     */
    private function getModelDetails(ReflectionClass|string $modelClass): ?array
    {
        if ($modelClass instanceof ReflectionClass) {
            $modelClass = $modelClass->getName();
        }

        return app(RunModelInspector::class)($modelClass);
    }

    private function overrideCollectionWithInterfaces(Collection $columns, Collection $interfaces): Collection
    {
        return $columns->filter(function ($column) use ($interfaces) {
            $includeColumn = true;

            $interfaces->each(function ($interface, $key) use ($column, &$includeColumn) {
                if ($key === $column['name']) {
                    $includeColumn = false;
                }
            });

            return $includeColumn;
        });
    }
}
