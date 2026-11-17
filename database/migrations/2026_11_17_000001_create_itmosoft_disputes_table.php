<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itmosoft_disputes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->morphs('subject');
            $table->morphs('claimant');
            $table->morphs('respondent');
            $table->string('category', 80)->nullable();
            $table->string('reason', 160);
            $table->text('description')->nullable();
            $table->string('status', 32)->default('open')->index();
            $table->string('priority', 20)->default('normal')->index();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('response_due_at')->nullable();
            $table->nullableMorphs('assigned_to');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['status', 'priority'], 'itmosoft_disputes_status_priority_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itmosoft_disputes');
    }
};
