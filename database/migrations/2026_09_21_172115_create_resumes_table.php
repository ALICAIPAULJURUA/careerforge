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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('name', 255);
            $table->string('target_role', 255)->nullable();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->constrained()->cascadeOnDelete();
            $table->string('font_override', 100)->nullable();
            $table->boolean('photo_enabled')->default(true);
            $table->enum('photo_style', ['none','circle','square','rounded'])->default('circle');
            $table->enum('layout', ['one_column','two_column','sidebar'])->default('one_column');
            $table->boolean('ats_mode')->default(false);
            $table->enum('page_size', ['a4','letter'])->default('a4');
            $table->string('slug', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
