<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->enum('type', ['SOAT', 'TECNOMECANICA', 'LICENCIA']);
            $table->string('document_number', 60);
            $table->date('issue_date')->nullable();
            $table->date('expiry_date');
            $table->string('pdf_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['vehicle_id', 'type'], 'idx_documents_vehicle_type');
            $table->index('vehicle_id', 'idx_documents_vehicle_id');
            $table->index('expiry_date', 'idx_documents_expiry_date');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
