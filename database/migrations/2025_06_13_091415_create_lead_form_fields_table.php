<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('lead_forms')->onDelete('cascade');
            $table->string('label');
            $table->string('name')->nullable();
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_mapped')->default(false);
            $table->json('options')->nullable();
            $table->string('placeholder')->nullable();
            $table->integer('order')->default(0);
            $table->string('validation_rules')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_form_fields');
    }
};
