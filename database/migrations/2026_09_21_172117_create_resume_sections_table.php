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
        Schema::create('resume_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete()->index();
            $table->enum('section_type', ['summary','experience','education','skills','projects','certifications','awards','leadership','languages','references']);
            $table->boolean('is_visible')->default(true);
            $table->smallInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->unique(['resume_id', 'section_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_sections');
    }
};
