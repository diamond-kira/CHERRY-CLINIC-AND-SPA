<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('staff')->restrictOnDelete();
            $table->string('status', 30)->default('in_progress');
            $table->text('presenting_complaint')->nullable();
            $table->longText('examination_notes')->nullable();
            $table->longText('diagnosis')->nullable();
            $table->longText('treatment_plan')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->restrictOnDelete();
            $table->foreignId('issued_by_staff_id')->constrained('staff')->restrictOnDelete();
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('route')->nullable();
            $table->string('frequency');
            $table->string('duration')->nullable();
            $table->string('quantity')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('spa_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('therapist_id')->constrained('staff')->restrictOnDelete();
            $table->string('status', 30)->default('in_progress');
            $table->longText('treatment_notes')->nullable();
            $table->json('products_used')->nullable();
            $table->longText('recommendations')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spa_service_records');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('consultations');
    }
};
