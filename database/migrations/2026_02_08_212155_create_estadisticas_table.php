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
        Schema::create('estadisticas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_producto')->onDelete('cascade');
            $table->integer('cantidad_vendida')->default(0);
            $table->decimal('ingresos_generados', 10, 2)->default(0);
            $table->date('fecha');
            $table->timestamps();
            
            // Índices
            $table->index('producto_id');
            $table->index('categoria_id');
            $table->index('fecha');
            $table->index(['producto_id', 'fecha']);
            $table->index(['categoria_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadisticas');
    }
};