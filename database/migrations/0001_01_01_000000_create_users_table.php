<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('id_type', ['CC', 'CE', 'PPT', 'PASAPORTE']);
            $table->string('id_number', 30)->unique();
            $table->string('phone_number', 20);
            $table->string('profile_pic_url', 500)->nullable();
            $table->boolean('terms_accepted')->default(false);
            $table->string('social_provider', 20)->nullable();
            $table->string('social_id', 255)->nullable();
            $table->string('fcm_token', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['social_provider', 'social_id'], 'idx_users_social');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
