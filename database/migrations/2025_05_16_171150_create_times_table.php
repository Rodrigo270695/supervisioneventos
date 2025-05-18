<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('time_type_id')->constrained()->restrictOnDelete();
            $table->time('start_time')->comment('Hora de inicio');
            $table->time('end_time')->nullable()->comment('Hora de fin (opcional)');
            $table->text('description')->nullable()->comment('Descripción o notas adicionales');
            $table->timestamps();

            // Índices para mejorar el rendimiento de las búsquedas
            $table->index(['event_id', 'time_type_id']);
            $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('times');
    }
};
