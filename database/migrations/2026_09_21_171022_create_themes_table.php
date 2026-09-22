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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('primary_color', 7);
            $table->string('secondary_color', 7);
            $table->string('text_color', 7);
            $table->string('background_color', 7);
            $table->string('sidebar_color', 7)->nullable();
            $table->string('font_family', 100)->default('Inter');
            $table->string('heading_size', 10)->default('md');
            $table->string('body_size', 10)->default('md');
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
