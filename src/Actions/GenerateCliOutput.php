<?php

namespace FumeApp\ModelTyper\Actions;

use FumeApp\ModelTyper\Internal\StringBuffer;
use FumeApp\ModelTyper\Writers\ModelRelationshipWriter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateCliOutput
{
    /**
     * @var array<int, ReflectionClass>
     */
    protected array $enumReflectors = [];

    /**
     * @var array<int,  array<string, mixed>>
     */
    protected array $imports = [];

    /**
     * Output the command in the CLI.
     *
     * @param  Collection<int, string>  $models
     * @param  array<string, string>  $mappings
     */
    public function __invoke(
        Collection $models,
        array $mappings,
        bool $global = false,
        bool $useEnums = false,
        bool $plurals = false,
        bool $apiResources = false,
        bool $optionalRelations = false,
        bool $noRelations = false,
        bool $noHidden = false,
        bool $optionalNullables = false,
        bool $fillables = false,
        string $fillableSuffix = 'Fillable',
        bool $fillableRelations = false
    ): string {
        $output = new StringBuffer;
        $imports = collect([]);
        $modelBuilder = app(BuildModelDetails::class);
        $colAttrWriter = app(WriteColumnAttribute::class);
        $relationWriter = new ModelRelationshipWriter(
            plurals: $plurals,
            optionalRelationship: $optionalRelations
        );

        if ($global) {
            $namespace = Config::get('modeltyper.global-namespace', 'models');
            $output->write('export {}' . PHP_EOL . 'declare global {' . PHP_EOL . "  export namespace {$namespace} {" . PHP_EOL . PHP_EOL);
            $output->setIndentLevel(4);
        }

        foreach ($models as $model) {
            $modelDetails = $modelBuilder($model);
            $name = $modelDetails->getName();

            // skip to next model if model details could not be resolved
            if ($modelDetails === null) {
                continue;
            }

            $imports = $imports->merge($modelDetails->getImports());
            $output->writeLn("export interface $name {");

            if ($modelDetails->getColumnAttributes()->isNotEmpty()) {
                $output->writeLn('  // columns');
                foreach ($modelDetails->getColumnAttributes() as $attribute) {
                    [$line, $enum] = $colAttrWriter(
                        reflectionModel: $modelDetails->getReflectionClass(),
                        attribute: $attribute,
                        mappings: $mappings,
                        indent: $output->getIndent(),
                        noHidden: $noHidden,
                        optionalNullables: $optionalNullables,
                        useEnums: $useEnums
                    );
                    if (! empty($line)) {
                        $output->write($line, false);
                        if ($enum) {
                            $this->enumReflectors[] = $enum;
                        }
                    }
                }
            }

            if ($modelDetails->getNonColumnAttributes()->isNotEmpty()) {
                $output->writeLn('  // mutators');
                foreach ($modelDetails->getNonColumnAttributes() as $attribute) {
                    [$line, $enum] = $colAttrWriter(
                        reflectionModel: $modelDetails->getReflectionClass(),
                        attribute: $attribute,
                        mappings: $mappings,
                        indent: $output->getIndent(),
                        noHidden: $noHidden,
                        optionalNullables: $optionalNullables,
                        useEnums: $useEnums
                    );
                    if (! empty($line)) {
                        $output->write($line, false);
                        if ($enum) {
                            $this->enumReflectors[] = $enum;
                        }
                    }
                }
            }

            if ($modelDetails->getIntefaces()->isNotEmpty()) {
                $output->writeLn('  // overrides');
                foreach ($modelDetails->getIntefaces() as $interface) {
                    [$line] = $colAttrWriter(
                        reflectionModel: $modelDetails->getReflectionClass(),
                        attribute: $interface,
                        mappings: $mappings,
                        indent: $output->getIndent()
                    );
                    $output->write($line, false);
                }
            }

            if ($modelDetails->getRelations()->isNotEmpty() && ! $noRelations) {
                $output->writeLn('  // relations');
                foreach ($modelDetails->getRelations() as $relation) {
                    $output->writeLn($relationWriter->write($relation));
                }
            }
            $output->writeLn('}');

            if ($plurals) {
                $plural = Str::plural($name);
                $output->writeLn("export type $plural = {$name}[]");

                if ($apiResources) {
                    $output->writeLn("export interface {$name}Results extends api.MetApiResults { data: $plural }");
                }
            }

            if ($apiResources) {
                $output->writeLn("export interface {$name}Result extends api.MetApiResults { data: $name }");
                $output->writeLn("export interface {$name}MetApiData extends api.MetApiData { data: $name }");
                $output->writeLn("export interface {$name}Response extends api.MetApiResponse { data: {$name}MetApiData }");
            }

            if ($fillables) {
                $output->writeLn(app(WriteFillables::class)(
                    reflectionModel: $modelDetails->getReflectionClass(),
                    relations: $modelDetails->getRelations(),
                    fillableRelations: $fillableRelations,
                    fillableSuffix: $fillableSuffix,
                    relationWriter: $relationWriter
                ));
            }
            $output->writeLn();
        }

        collect($this->enumReflectors)
            ->unique(fn (ReflectionClass $reflector) => $reflector->getName())
            ->each(function (ReflectionClass $reflector) use ($output, $useEnums) {
                $enum = app(WriteEnumConst::class)($reflector, $output->getIndent(), false, $useEnums);
                $output->write($enum, false);
            });

        collect($this->imports)
            ->unique()
            ->each(function ($import) use ($output) {
                $importTypeWithoutGeneric = Str::before($import['type'], '<');
                $output->prepend("import { {$importTypeWithoutGeneric} } from '{$import['import']}'" . PHP_EOL);
            });

        if ($global) {
            $output->write('  }' . PHP_EOL . '}' . PHP_EOL . PHP_EOL, false);
        }

        return $output->trim()->printLn();
    }
}
