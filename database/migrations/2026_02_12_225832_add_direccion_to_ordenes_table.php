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
        Schema::table('ordenes', function (Blueprint $table) {
            // Agregar campo de dirección si no existe
            if (!Schema::hasColumn('ordenes', 'direccion_id')) {
                $table->foreignId('direccion_id')->nullable()->after('user_id')
                    ->constrained('direcciones_entrega')->onDelete('set null');
            }
            
            // Agregar campo de notas si no existe
            if (!Schema::hasColumn('ordenes', 'notas')) {
                $table->text('notas')->nullable()->after('fecha_entrega_estimada');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            if (Schema::hasColumn('ordenes', 'direccion_id')) {
                $table->dropForeign(['direccion_id']);
                $table->dropColumn('direccion_id');
            }
            
            if (Schema::hasColumn('ordenes', 'notas')) {
                $table->dropColumn('notas');
            }
        });
    }
};