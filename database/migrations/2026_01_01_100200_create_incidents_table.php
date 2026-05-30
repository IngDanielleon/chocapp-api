<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vehicle_id');
            $table->string('title', 200);
            $table->text('description');
            $table->date('incident_date');
            $table->time('incident_time');
            $table->string('location_address', 500);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('weather_condition', ['SOLEADO', 'LLUVIOSO', 'NUBLADO', 'NOCHE']);
            $table->enum('road_condition', ['BUEN_ESTADO', 'HUMEDO', 'HUECOS', 'DERRUMBE']);
            $table->string('police_report_number', 60)->nullable();
            $table->enum('status', ['BORRADOR', 'REPORTADO', 'EN_REVISION', 'FINALIZADO'])->default('BORRADOR');
            $table->string('report_pdf_url', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id', 'idx_incidents_user_id');
            $table->index('vehicle_id', 'idx_incidents_vehicle_id');
            $table->index('status', 'idx_incidents_status');
            $table->index('incident_date', 'idx_incidents_date');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
