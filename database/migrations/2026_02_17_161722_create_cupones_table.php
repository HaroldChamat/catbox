<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->enum('tipo', ['porcentaje', 'monto_fijo']);
            $table->decimal('valor', 10, 2); // % o monto
            $table->enum('alcance', ['tienda', 'categoria', 'productos'])->default('tienda');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_producto')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->integer('limite_usos')->nullable(); // null = ilimitado
            $table->integer('usos_actuales')->default(0);
            $table->timestamp('fecha_expiracion')->nullable();
            $table->timestamps();
        });

        // Tabla pivote cupón ↔ productos específicos
        Schema::create('cupon_producto', function (Blueprint $table) {
            $table->foreignId('cupon_id')->constrained('cupones')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->primary(['cupon_id', 'producto_id']);
        });

        // Tabla para registrar qué usuario usó qué cupón
        Schema::create('cupon_uso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cupon_id')->constrained('cupones')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('orden_id')->nullable()->constrained('ordenes')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cupon_uso');
        Schema::dropIfExists('cupon_producto');
        Schema::dropIfExists('cupones');
    }
};