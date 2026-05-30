<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title', 200);
            $table->text('body');
            $table->enum('type', [
                'ALERT', 'INFO', 'DOCUMENT_EXPIRING',
                'MAINTENANCE_REMINDER', 'INCIDENT_UPDATE',
            ]);
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('user_id', 'idx_notifications_user_id');
            $table->index(['user_id', 'is_read'], 'idx_notifications_is_read');
            $table->index('type', 'idx_notifications_type');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
