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
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->foreignId('host_type_id')->constrained()->restrictOnDelete();
            $table->string('nombres', 40);
            $table->string('apellidos', 40);
            $table->string('dni', 8);
            $table->unsignedTinyInteger('edad')->nullable();
            $table->string('correo')->nullable();
            $table->timestamps();

            // Índices para optimizar búsquedas
            $table->index('dni');
            $table->index(['nombres', 'apellidos']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};
