<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('plate', 10)->unique();
            $table->string('brand', 60);
            $table->string('model', 60);
            $table->unsignedSmallInteger('year');
            $table->string('color', 40);
            $table->enum('type', ['MOTOCICLETA', 'AUTOMOVIL'])->default('AUTOMOVIL');
            $table->string('photo_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id', 'idx_vehicles_user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
