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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes')->onDelete('cascade');
            $table->enum('metodo_pago', ['tarjeta', 'paypal'])->default('tarjeta');
            $table->decimal('monto', 10, 2);
            $table->enum('estado', ['pendiente', 'completado', 'fallido', 'reembolsado'])->default('pendiente');
            $table->string('transaction_id', 255)->nullable();
            $table->json('datos_pago')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('orden_id');
            $table->index('estado');
            $table->index('transaction_id');
            $table->unique('orden_id'); // Una orden solo tiene un pago
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};