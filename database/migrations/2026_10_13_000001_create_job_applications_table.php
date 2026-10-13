<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('location')->nullable();
            $table->string('url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('wishlist')->index();
            $table->date('applied_on')->nullable();
            $table->dateTime('interview_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('status_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_changes');
        Schema::dropIfExists('job_applications');
    }
};
