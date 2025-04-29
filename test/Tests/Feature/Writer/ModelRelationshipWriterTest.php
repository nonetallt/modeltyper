<?php

namespace Tests\Feature\Writer;

use App\Models\User;
use FumeApp\ModelTyper\Internal\ModelRelation;
use FumeApp\ModelTyper\Writers\ModelRelationshipWriter;
use ReflectionClass;
use Tests\TestCase;

class ModelRelationshipWriterTest extends TestCase
{
    protected ?ModelRelation $relation = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize the sample relation
        $this->relation = ModelRelation::createFromArray([
            'model' => new ReflectionClass(User::class),
            'name' => 'notifications',
            'type' => 'MorphMany',
            'related' => "Illuminate\Notifications\DatabaseNotification",
        ]);
    }

    public function test_writer_can_return_array()
    {
        $writer = new ModelRelationshipWriter(jsonOutput: true);
        $result = $writer->write($this->relation);

        $this->assertIsArray($result);
        $this->assertEquals(['name' => 'notifications', 'type' => 'DatabaseNotification[]'], $result);
    }

    public function test_writer_can_return_optional_relationships()
    {
        $writer = new ModelRelationshipWriter(jsonOutput: false, optionalRelationship: true);
        $result = $writer->write($this->relation);

        $this->assertSame('  notifications?: DatabaseNotification[]', $result);
    }

    public function test_writer_can_return_optional_relationships_as_array()
    {
        $writer = new ModelRelationshipWriter(jsonOutput: true, optionalRelationship: true);
        $result = $writer->write($this->relation);

        $this->assertEquals(['name' => 'notifications?', 'type' => 'DatabaseNotification[]'], $result);
    }

    public function test_writer_can_return_plural_relationships()
    {
        $writer = new ModelRelationshipWriter(jsonOutput: false, plurals: true);
        $result = $writer->write($this->relation);

        $this->assertStringContainsString('notifications: DatabaseNotifications', $result);
    }

    public function test_writer_config_is_immutable()
    {
        $originalWriter = new ModelRelationshipWriter(suffix: 'foo', optionalRelationship: false);
        $newWriter = $originalWriter->setSuffix('bar')->setOptional(true);
        $testedKeys = ['suffix', 'optionalRelationship'];

        // Assert that config remains same for the original writer
        $this->assertSame(
            ['optionalRelationship' => false, 'suffix' => 'foo'],
            array_intersect_key($originalWriter->toArray(), array_flip($testedKeys))
        );

        // Assert that config was changed for new writer
        $this->assertSame(
            ['optionalRelationship' => true, 'suffix' => 'bar'],
            array_intersect_key($newWriter->toArray(), array_flip($testedKeys))
        );
    }
}
