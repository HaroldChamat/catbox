<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('motivo');
            $table->text('respuesta_admin')->nullable();
            $table->decimal('monto_total', 10, 2)->default(0);
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_rechazo')->nullable();
            $table->timestamps();
        });

        // Items de la devolución (productos específicos)
        Schema::create('devolucion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->onDelete('cascade');
            $table->foreignId('detalle_orden_id')->constrained('detalles_orden')->onDelete('cascade');
            $table->integer('cantidad'); // Cantidad a devolver
            $table->decimal('monto', 10, 2); // Subtotal de esta devolución
            $table->timestamps();
        });

        // Créditos generados por devoluciones
        Schema::create('creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('devolucion_id')->constrained('devoluciones')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo', 10, 2); // Saldo restante
            $table->boolean('usado')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('creditos');
        Schema::dropIfExists('devolucion_items');
        Schema::dropIfExists('devoluciones');
    }
};