<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->tinyInteger('calificacion')->unsigned(); // 1-5
            $table->text('comentario');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->timestamps();
            $table->unique(['user_id', 'producto_id']); // Una reseña por usuario por producto
        });
    }

    public function down(): void {
        Schema::dropIfExists('resenas');
    }
};