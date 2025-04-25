<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('third_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('second_level_id')->constrained('second_level');
        });
    }
};
