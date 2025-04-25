<?php

namespace Tests\Feature\Actions;

use App\Models\FirstLevel;
use FumeApp\ModelTyper\Actions\BuildModelDetails;
use FumeApp\ModelTyper\Actions\WriteFillables;
use FumeApp\ModelTyper\Writers\ModelRelationshipWriter;
use ReflectionClass;
use Tests\TestCase;
use Tests\Traits\UsesInputFiles;

class WriteFillablesTest extends TestCase
{
    use UsesInputFiles;

    public function test_action_can_be_resolved_by_application()
    {
        $this->assertInstanceOf(WriteFillables::class, resolve(WriteFillables::class));
    }

    public function test_action_writes_relation_correctly()
    {
        $model = new ReflectionClass(FirstLevel::class);
        $relations = app(BuildModelDetails::class)($model)->getRelations();

        $result = app(WriteFillables::class)(
            reflectionModel: $model,
            relations: $relations,
            fillableRelations: true,
            fillableSuffix: 'Editable',
            relationWriter: new ModelRelationshipWriter()
        );

        $expected = $this->getExpectedContent('fillable-relation.ts');

        // The trim for expected output here is to account for file's trailing EOL
        $this->assertSame(trim($expected), $result);
    }
}
