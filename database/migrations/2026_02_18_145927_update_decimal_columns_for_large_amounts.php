<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->decimal('precio_unitario', 15, 2)->change();
            $table->decimal('subtotal', 15, 2)->change();
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->decimal('total', 15, 2)->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio', 15, 2)->change();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('monto', 15, 2)->change();
        });

        Schema::table('devoluciones', function (Blueprint $table) {
            $table->decimal('monto_total', 15, 2)->change();
        });

        Schema::table('devolucion_items', function (Blueprint $table) {
            $table->decimal('monto', 15, 2)->change();
        });

        Schema::table('creditos', function (Blueprint $table) {
            $table->decimal('monto', 15, 2)->change();
            $table->decimal('saldo', 15, 2)->change();
        });
    }

    public function down(): void {
        Schema::table('detalles_orden', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->change();
            $table->decimal('subtotal', 10, 2)->change();
        });

        Schema::table('ordenes', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->decimal('precio', 10, 2)->change();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('monto', 10, 2)->change();
        });

        Schema::table('devoluciones', function (Blueprint $table) {
            $table->decimal('monto_total', 10, 2)->change();
        });

        Schema::table('devolucion_items', function (Blueprint $table) {
            $table->decimal('monto', 10, 2)->change();
        });

        Schema::table('creditos', function (Blueprint $table) {
            $table->decimal('monto', 10, 2)->change();
            $table->decimal('saldo', 10, 2)->change();
        });
    }
};