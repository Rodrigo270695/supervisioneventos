<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 40);
            $table->string('last_name', 40);
            $table->string('dni', 8)->nullable();
            $table->integer('table_number');
            $table->integer('passes')->default(1);
            $table->integer('used_passes')->default(0);
            $table->string('qr_code')->unique();
            $table->timestamp('last_access')->nullable();
            $table->timestamps();

            // Índices
            $table->index('event_id');
            $table->index('dni');
            $table->index('qr_code');
        });

        // Tabla para registrar los accesos de invitados
        Schema::create('guest_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->integer('people_count');
            $table->timestamp('access_datetime');
            $table->string('access_type'); // 'entry' or 'exit'
            $table->text('observations')->nullable();
            $table->timestamps();

            // Índices
            $table->index('guest_id');
            $table->index('event_id');
            $table->index('access_datetime');
        });
    }

    public function down()
    {
        Schema::dropIfExists('guest_accesses');
        Schema::dropIfExists('guests');
    }
};
