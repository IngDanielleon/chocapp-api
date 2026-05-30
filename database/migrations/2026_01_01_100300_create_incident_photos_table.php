<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('incident_id');
            $table->enum('angle', [
                'FRONT', 'FRONT_RIGHT', 'RIGHT', 'REAR_RIGHT',
                'REAR', 'REAR_LEFT', 'LEFT', 'FRONT_LEFT',
                'INTERIOR', 'ODOMETER', 'EXTRA',
            ]);
            $table->string('image_url', 500);
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();

            $table->index('incident_id', 'idx_incident_photos_incident_id');
            $table->index(['incident_id', 'angle'], 'idx_incident_photos_angle');
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_photos');
    }
};
