<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('third_parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('incident_id');
            $table->enum('party_type', ['VEHICULO', 'PEATON', 'CICLISTA']);
            $table->string('plate', 10)->nullable();
            $table->string('brand', 60)->nullable();
            $table->string('model', 60)->nullable();
            $table->string('color', 40)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_id', 30)->nullable();
            $table->string('driver_phone', 20)->nullable();
            $table->string('insurance_company', 100)->nullable();
            $table->string('insurance_policy', 60)->nullable();
            $table->timestamps();

            $table->index('incident_id', 'idx_third_parties_incident_id');
            $table->foreign('incident_id')->references('id')->on('incidents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('third_parties');
    }
};
