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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')
                  ->constrained('categorias_producto')
                  ->onDelete('restrict')  // ← CAMBIADO de cascade a restrict
                  ->onUpdate('cascade');
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->string('slug', 255)->nullable();  // ← AGREGADO: columna slug
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('categoria_id');
            $table->index('activo');
            $table->index('slug');  // ← AGREGADO: índice para slug
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};