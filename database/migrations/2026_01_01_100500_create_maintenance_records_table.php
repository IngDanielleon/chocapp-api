<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->date('maintenance_date');
            $table->enum('type', [
                'ACEITE', 'FRENOS', 'LLANTAS', 'BATERIA',
                'FILTROS', 'SUSPENSION', 'REVISION_GENERAL', 'OTRO',
            ]);
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('workshop_name', 150)->nullable();
            $table->unsignedInteger('current_mileage')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_date')->nullable();
            $table->unsignedInteger('next_mileage')->nullable();
            $table->timestamps();

            $table->index('vehicle_id', 'idx_maintenance_vehicle_id');
            $table->index('maintenance_date', 'idx_maintenance_date');
            $table->index(['vehicle_id', 'type'], 'idx_maintenance_type');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};
