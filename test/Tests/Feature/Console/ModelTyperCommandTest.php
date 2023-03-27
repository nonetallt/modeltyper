<?php

namespace Tests\Feature\Console;

use Tests\Feature\TestCase;
use Tests\Traits\GeneratesOutput;

class ModelTyperCommandTest extends TestCase
{
    use GeneratesOutput;

    protected function tearDown() : void
    {
        parent::tearDown();
        $this->deleteOutput();
    }

    public function testBaseCommandCanBeExecutedSuccessfully()
    {
        $this->artisan('model:typer')->assertSuccessful();
    }
}
