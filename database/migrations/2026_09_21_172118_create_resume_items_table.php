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
        Schema::create('resume_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_section_id')->constrained('resume_sections')->cascadeOnDelete()->index();
            $table->string('itemable_type', 255);
            $table->unsignedBigInteger('itemable_id');
            $table->smallInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();

            $table->index(['itemable_type', 'itemable_id']);
            $table->unique(['resume_section_id', 'itemable_type', 'itemable_id'], 'resume_items_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_items');
    }
};
