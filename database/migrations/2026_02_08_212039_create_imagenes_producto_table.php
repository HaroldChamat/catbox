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
        Schema::create('imagenes_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade')    // ← Esto está BIEN - cuando se borra un producto, sí queremos borrar sus imágenes
                  ->onUpdate('cascade');
            $table->string('ruta', 500);
            $table->integer('orden')->default(0);
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
            
            // Índices
            $table->index('producto_id');
            $table->index(['producto_id', 'es_principal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagenes_producto');
    }
};