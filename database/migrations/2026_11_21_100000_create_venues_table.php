<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('location')->nullable();

            // A range (e.g. 150–200) or a single number (min left empty).
            $table->unsignedInteger('capacity_min')->nullable();
            $table->unsignedInteger('capacity_max');

            $table->text('amenities')->nullable();   // one per line
            $table->text('rules')->nullable();
            $table->string('image_path')->nullable();

            // active | maintenance | deactivated
            $table->string('status')->default('active')->index();
            $table->string('status_note')->nullable();
            $table->date('available_from')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
