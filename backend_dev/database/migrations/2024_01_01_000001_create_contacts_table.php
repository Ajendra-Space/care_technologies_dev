<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('status', ['active', 'merged', 'inactive'])->default('active');
            $table->foreignId('merged_into_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->text('merge_history')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_type')->default('text');
            $table->text('field_options')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_field_id')->constrained()->cascadeOnDelete();
            $table->text('field_value');
            $table->timestamps();
            $table->unique(['contact_id', 'custom_field_id']);
        });

        Schema::create('contact_additional_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('contact_additional_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('phone');
            $table->timestamps();
        });

        Schema::create('contact_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_merge_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_contact_id')->constrained('contacts');
            $table->foreignId('merged_contact_id')->constrained('contacts');
            $table->text('merge_details')->nullable();
            $table->timestamp('merged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_merge_history');
        Schema::dropIfExists('contact_files');
        Schema::dropIfExists('contact_additional_phones');
        Schema::dropIfExists('contact_additional_emails');
        Schema::dropIfExists('contact_custom_fields');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('contacts');
    }
};

