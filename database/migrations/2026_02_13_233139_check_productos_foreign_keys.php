<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verificar y corregir configuraciones de llaves foráneas
     */
    public function up(): void
    {
        // Verificar la configuración actual de la tabla productos
        Schema::table('productos', function (Blueprint $table) {
            // No hacer nada aquí, solo verificar que existe
        });
        
        // Log de la configuración actual
        $tableInfo = DB::select("SHOW CREATE TABLE productos");
        \Log::info('Configuración actual de tabla productos:', (array)$tableInfo);
        
        // Verificar restricciones de llave foránea
        $foreignKeys = DB::select("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                DELETE_RULE,
                UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE
            JOIN information_schema.REFERENTIAL_CONSTRAINTS USING (CONSTRAINT_NAME)
            WHERE TABLE_NAME = 'productos'
            AND TABLE_SCHEMA = DATABASE()
        ");
        
        \Log::info('Llaves foráneas de productos:', (array)$foreignKeys);
        
        // Verificar si la llave foránea está configurada como CASCADE
        foreach ($foreignKeys as $fk) {
            if ($fk->COLUMN_NAME === 'categoria_id' && $fk->DELETE_RULE === 'CASCADE') {
                \Log::warning('PROBLEMA ENCONTRADO: La llave foránea categoria_id tiene DELETE CASCADE');
                \Log::warning('Esto podría causar borrados en cascada no deseados');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada
    }
};