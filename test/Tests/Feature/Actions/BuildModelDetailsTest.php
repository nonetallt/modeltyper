<?php

namespace Tests\Feature\Actions;

use App\Models\User;
use FumeApp\ModelTyper\Actions\BuildModelDetails;
use FumeApp\ModelTyper\Actions\GetModels;
use FumeApp\ModelTyper\Internal\ModelDetails;
use Tests\TestCase;

class BuildModelDetailsTest extends TestCase
{
    public function test_action_can_be_resolved_by_application()
    {
        $this->assertInstanceOf(BuildModelDetails::class, resolve(BuildModelDetails::class));
    }

    public function test_action_can_be_executed()
    {
        $modelClasses = app(GetModels::class)(User::class);
        $action = app(BuildModelDetails::class);
        $result = $action($modelClasses->first());

        $this->assertInstanceOf(ModelDetails::class, $result);
    }

    public function test_action_returns_relationships_for_user_model()
    {
        $modelClasses = app(GetModels::class)(User::class);
        $action = app(BuildModelDetails::class);

        $userClass = $modelClasses->first();
        $this->assertSame(User::class, $userClass);

        $result = $action($userClass);
        $this->assertInstanceOf(ModelDetails::class, $result);
        $this->assertSame(['notifications'], $result->getRelations()->map(fn($relation) => $relation->getName())->toArray());
    }
}
